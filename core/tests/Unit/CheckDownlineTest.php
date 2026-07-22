<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Matrix;
use App\Models\User;

/**
 * Test suite for checkDownline() function
 * Ensures genealogy checks work correctly at scale
 */
class CheckDownlineTest extends TestCase
{
    /**
     * Test 1: Parent is NOT in downline (immediate check)
     */
    public function test_returns_false_when_parent_id_not_in_downline()
    {
        $result = checkDownline(1, 999);
        $this->assertFalse($result);
    }

    /**
     * Test 2: Parent IS in downline (direct child)
     */
    public function test_returns_true_when_parent_is_direct_child()
    {
        // Create user matrix tree:
        // User 1 (root)
        //   ├─ left: 2
        //   └─ right: 3
        
        Matrix::factory()->create([
            'stage_id' => 1,
            'user_id' => 1,
            'parent_id' => 0,
            'left' => 2,
            'right' => 3,
            'is_active' => 1,
        ]);
        
        Matrix::factory()->create([
            'stage_id' => 1,
            'user_id' => 2,
            'parent_id' => 1,
            'left' => 0,
            'right' => 0,
            'is_active' => 1,
        ]);
        
        Matrix::factory()->create([
            'stage_id' => 1,
            'user_id' => 3,
            'parent_id' => 1,
            'left' => 0,
            'right' => 0,
            'is_active' => 1,
        ]);
        
        $this->assertTrue(checkDownline(1, 2));
        $this->assertTrue(checkDownline(1, 3));
    }

    /**
     * Test 3: Parent is in downline at deeper level
     */
    public function test_returns_true_when_parent_is_nested_in_downline()
    {
        // Create tree:
        // User 1
        //   ├─ 2
        //   │  ├─ 4
        //   │  └─ 5
        //   └─ 3
        
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 1, 'parent_id' => 0, 'left' => 2, 'right' => 3]);
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 2, 'parent_id' => 1, 'left' => 4, 'right' => 5]);
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 3, 'parent_id' => 1, 'left' => 0, 'right' => 0]);
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 4, 'parent_id' => 2, 'left' => 0, 'right' => 0]);
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 5, 'parent_id' => 2, 'left' => 0, 'right' => 0]);
        
        // User 4 and 5 are 2 levels deep in user 1's tree
        $this->assertTrue(checkDownline(1, 4));
        $this->assertTrue(checkDownline(1, 5));
        
        // User 2 and 3 are 1 level deep
        $this->assertTrue(checkDownline(1, 2));
        $this->assertTrue(checkDownline(1, 3));
        
        // User 4,5 are NOT in user 3's downline
        $this->assertFalse(checkDownline(3, 4));
        $this->assertFalse(checkDownline(3, 5));
    }

    /**
     * Test 4: Invalid inputs (same user, zero/negative IDs)
     */
    public function test_returns_false_for_invalid_inputs()
    {
        // Same user
        $this->assertFalse(checkDownline(1, 1));
        
        // Zero or negative IDs
        $this->assertFalse(checkDownline(0, 1));
        $this->assertFalse(checkDownline(1, 0));
        $this->assertFalse(checkDownline(-1, 2));
        $this->assertFalse(checkDownline(1, -2));
    }

    /**
     * Test 5: Non-existent sponsor (no matrix entry)
     */
    public function test_returns_false_when_sponsor_not_in_matrix()
    {
        $result = checkDownline(99999, 1);
        $this->assertFalse($result);
    }

    /**
     * Test 6: Only stage_id=1 is checked
     */
    public function test_ignores_other_stages()
    {
        // Create matrix entry in stage 2 only
        Matrix::factory()->create([
            'stage_id' => 2,
            'user_id' => 100,
            'parent_id' => 0,
            'left' => 101,
            'right' => 102,
        ]);
        
        // Should not find downline in stage 2
        $this->assertFalse(checkDownline(100, 101));
    }

    /**
     * Test 7: Large tree performance (100+ nodes)
     */
    public function test_handles_large_tree_efficiently()
    {
        // Build a tree with 100+ nodes
        $this->buildLargeTree(1, 100);
        
        // Perform multiple downline checks
        $start = microtime(true);
        
        for ($i = 2; $i <= 50; $i++) {
            checkDownline(1, $i);
        }
        
        $elapsed = microtime(true) - $start;
        
        // 50 checks should complete in < 5 seconds
        $this->assertLessThan(5, $elapsed, 'Large tree search took too long');
    }

    /**
     * Test 8: Handles orphaned records gracefully
     */
    public function test_handles_orphaned_nodes()
    {
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 1, 'parent_id' => 0, 'left' => 2, 'right' => 0]);
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 2, 'parent_id' => 1, 'left' => 0, 'right' => 0]);
        
        // Even if node 3 exists but isn't in tree
        Matrix::factory()->create(['stage_id' => 1, 'user_id' => 3, 'parent_id' => 999, 'left' => 0, 'right' => 0]);
        
        $this->assertTrue(checkDownline(1, 2));
        $this->assertFalse(checkDownline(1, 3));
    }

    /**
     * Helper: Build a large binary tree for testing
     */
    private function buildLargeTree($rootId, $nodeCount)
    {
        $queue = [['user_id' => $rootId, 'parent_id' => 0]];
        $nodeId = $rootId + 1;
        
        Matrix::factory()->create([
            'stage_id' => 1,
            'user_id' => $rootId,
            'parent_id' => 0,
            'left' => $nodeId,
            'right' => $nodeId + 1,
        ]);
        
        while (!empty($queue) && $nodeId <= $nodeCount) {
            $parent = array_shift($queue);
            $leftChild = $nodeId++;
            $rightChild = $nodeId++;
            
            // Create parent->left->right connection
            Matrix::factory()->create([
                'stage_id' => 1,
                'user_id' => $leftChild,
                'parent_id' => $parent['user_id'],
                'left' => ($nodeId <= $nodeCount) ? $nodeId : 0,
                'right' => ($nodeId + 1 <= $nodeCount) ? $nodeId + 1 : 0,
            ]);
            
            if ($rightChild <= $nodeCount) {
                Matrix::factory()->create([
                    'stage_id' => 1,
                    'user_id' => $rightChild,
                    'parent_id' => $parent['user_id'],
                    'left' => 0,
                    'right' => 0,
                ]);
            }
            
            if ($nodeId + 1 <= $nodeCount) {
                $queue[] = ['user_id' => $leftChild, 'parent_id' => $parent['user_id']];
            }
        }
    }
}

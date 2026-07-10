<?php
public function getUplineTree(User $user, int $stageLevel)
    {
        $upline = collect();
        // Get the starting matrix with stage filtering
        $currentMatrix = Matrix::where('user_id', $user->id)
            ->whereHas('stage', function($query) use ($stageLevel) {
                $query->where('level', $stageLevel);
            })
            ->first();



        if (!$currentMatrix) {
            return $upline;
        }

        $parentMatrixes = $currentMatrix->parentMatric();
        //dd($parentMatrixes);
        if (!$parentMatrixes) {
            return $upline;
        } 


        
        if ($parentMatrixes){   
            $upline->push($parentMatrixes);
            if ($parentMatrixes->parent_id) {
                $pM1 = $parentMatrixes->parentMatric();
                if ($pM1){   
                    $upline->push($pM1);
                    if ($pM1->parent_id) {
                        $pM2 = $pM1->parentMatric();
                        if($pM2){   
                            $upline->push($pM2);
                            if($pM2->parent_id) {
                                $pM3 = $pM2->parentMatric();
                                if($pM3){   
                                    $upline->push($pM3);
                                    if($pM3->parent_id) {
                                        $pM4 = $pM3->parentMatric();
                                        if($pM4){   
                                            $upline->push($pM4);
                                            if($pM4->parent_id) {
                                                $pM4 = $pM4->parentMatric();
                                                if($pM4){   
                                                    $upline->push($pM4);
                                                    if($pM4->parent_id) {
                                                        $pM5 = $pM4->parentMatric();
                                                        if($pM5)
                                                            $upline->push($pM5);
                                                            if($pM5->parent_id) {
                                                                $pM6 = $pM5->parentMatric();
                                                                if($pM6)
                                                                    $upline->push($pM6);
                                                                    if($pM6->parent_id) {
                                                                        $pM7 = $pM6->parentMatric();
                                                                        if($pM7)
                                                                            $upline->push($pM7);
                                                                            if($pM7->parent_id) {
                                                                                $pM8 = $pM7->parentMatric();
                                                                                if($pM8)
                                                                                    $upline->push($pM8);
                                                                                    if($pM8->parent_id) {
                                                                                        $pM9 = $pM8->parentMatric();
                                                                                        if($pM9)
                                                                                            $upline->push($pM9);
                                                                                            if($pM9->parent_id) {
                                                                                                $pM_10 = $pM9->parentMatric();
                                                                                                if($pM_10)
                                                                                                    $upline->push($pM_10);
                                                                                                    if($pM_10->parent_id) {
                                                                                                        $pM_11 = $pM_10->parentMatric();
                                                                                                        if($pM_11)
                                                                                                            $upline->push($pM_11);
                                                                                                            if($pM_11->parent_id) {
                                                                                                                $pM_12 = $pM_11->parentMatric();
                                                                                                                if($pM_12)
                                                                                                                    $upline->push($pM_12);
                                                                                                                    
                                                                                                            }
                                                                                                            
                                                                                                    }
                                                                                                  
                                                                                            }
                                                                                            
                                                                                            
                                                                                    }
                                                                                
                                                                                    
                                                                                    
                                                                            }
                                                                            
                                                                            
                                                                    }
                                                                
                                                            }
                                                    }
                                                }

                                                

                                            }
                                        }


                                    }
                                }

                            }
                        }

                    }
                }

            }
        }

        return $upline;
    }
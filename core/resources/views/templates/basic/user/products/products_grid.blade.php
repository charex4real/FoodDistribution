@foreach($products as $product)
@php
    $statePrice   = $product->statePrices->first();
    $displayPrice = $statePrice ? $statePrice->price : $product->price;
    $isStatePrice = (bool) $statePrice;
@endphp
<div class="shop-card" data-product-id="{{ $product->id }}">

    {{-- Image --}}
    <div class="shop-card-img">
        @if($product->thumbnail)
            <img src="{{ getImage(getFilePath('products') . '/' . $product->thumbnail, getFileSize('products')) }}" alt="{{ $product->name }}">
        @else
            <div class="shop-card-img-placeholder">
                <i class="las la-box"></i>
            </div>
        @endif
        <span class="shop-card-cat">{{ $product->category->name }}</span>
    </div>

    {{-- Body --}}
    <div class="shop-card-body">
        <h6 class="shop-card-name">{{ $product->name }}</h6>
        <p class="shop-card-desc">{{ Str::limit($product->description, 80) }}</p>

        <div class="shop-card-footer">
            <div class="shop-card-price-wrap">
                @if($selectedState)
                    <span class="shop-card-price">₦{{ number_format($displayPrice, 2) }}</span>
                    @if($isStatePrice)
                        <span class="shop-card-price-badge state">State Price</span>
                    @else
                        <span class="shop-card-price-badge default">Default</span>
                    @endif
                @else
                    <span class="shop-card-price">₦{{ number_format($product->price, 2) }}</span>
                    <span class="shop-card-price-hint"><i class="las la-map-marker-alt"></i> Select state</span>
                @endif
            </div>

            @if($selectedState)
                <button class="shop-card-btn add-to-cart" data-product-id="{{ $product->id }}">
                    <i class="las la-shopping-cart"></i>
                </button>
            @else
                <button class="shop-card-btn disabled" disabled title="Select a state first">
                    <i class="las la-map-marker-alt"></i>
                </button>
            @endif
        </div>
    </div>
</div>
@endforeach

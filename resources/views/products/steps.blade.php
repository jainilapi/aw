@php

    if ($type == 'simple') {
        $steps = ['Basics', 'Pack Sizes', 'Pricing', 'Inventory', 'Category', 'Substitutes', 'Review'];
    } else if ($type == 'variable') {
        $steps = ['Basics', 'Variants', 'Pack Sizes', 'Pricing', 'Inventory', 'Category', 'Substitutes', 'Review'];
    } else {
        $steps = ['Basics', 'Bundle Items', 'Review'];
    }
@endphp

<div class="progress-container">
    <div class="step-progress" style="margin-top: 110px;margin-bottom: 20px;">
        <div class="step-line"></div>
        @foreach ($steps as $tstep)
            <div class="step-item @if($currentStep == $loop->iteration) active @elseif($loop->iteration >=1 && $currentStep > ($loop->iteration - 1)) completed @else pending @endif">
                @if($type == 'variable' && $product->variants()->count() > 0)
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt($loop->iteration), 'id' => encrypt($product->id)]) }}">
                        <div class="step-circle"> {{ $loop->iteration }} </div>
                    </a>
                    <a href="{{ route('product-management', ['type' => encrypt($type), 'step' => encrypt($loop->iteration), 'id' => encrypt($product->id)]) }}">
                        <div class="step-label"> {{ $tstep }} </div>
                    </a>
                @else
                <div class="step-circle"> {{ $loop->iteration }} </div>
                <div class="step-label"> {{ $tstep }} </div>
                @endif
            </div>
        @endforeach

    </div>
</div>
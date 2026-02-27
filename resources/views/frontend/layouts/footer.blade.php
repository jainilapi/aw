@php
$footer_setting = \App\Models\HomePageSetting::where('key', 'footer')->value('value');
@endphp
<footer>
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-lg-6 col-xl-3 col-md-6 col-xxl-3">
                    <div class="footer-box">
                        <h3>{{ $footer_setting->about_title ?? 'About Anjo Wholesale' }}</h3>
                        <p class="mt-2 mb-2">{{ $footer_setting->about_subtitle ?? 'Mon-Fri 8AM-4PM' }}</p>
                        <div class="ftr-whl">
                            <p>{!! nl2br(e($footer_setting->about_description ?? '')) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-lg-6 col-xl-3 col-md-6 col-xxl-3">
                    <div class="footer-box">
                        <h3>{{ $footer_setting->phone_title ?? 'Phone' }}</h3>
                        <div class="ftor-call">
                            @if(!empty($footer_setting->phone_numbers))
                                @foreach(explode("\n", $footer_setting->phone_numbers) as $phone)
                                    <p>{{ trim($phone) }}</p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-lg-6 col-xl-3 col-md-6 col-xxl-3">
                    <div class="footer-box">
                        <h3>{{ $footer_setting->email_title ?? 'Email' }}</h3>
                        <ul class="ftr-ul">
                             @if(!empty($footer_setting->emails))
                                @foreach(explode("\n", $footer_setting->emails) as $email)
                                    <li><a href="mailto:{{ trim($email) }}">{{ trim($email) }}</a></li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-lg- 3col-lg-6 col-xl-3 col-md-6 col-xxl-3">
                    <div class="footer-box">
                        <h3>{{ $footer_setting->address_title ?? 'P.O. Box' }}</h3>
                        <div class="ftr-po">
                            {!! nl2br(e($footer_setting->address_content ?? '')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xl-2">
                    <div class="ftr-social">
                        @if(!empty($footer_setting->facebook_url))
                            <a href="{{ $footer_setting->facebook_url }}"><img src="{{ asset('assets/images/Facebook.svg') }}" alt=""></a>
                        @endif
                        @if(!empty($footer_setting->instagram_url))
                            <a href="{{ $footer_setting->instagram_url }}"><img src="{{ asset('assets/images/Instagram.svg') }}" alt=""></a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-8 col-xl-10">
                    <ul>
                        <li>
                            <img src="{{ asset('assets/images/Subtract.svg') }}" alt="">
                            {{ $footer_setting->bottom_text ?? 'Anjo Wholesale' }}
                        </li>
                        @if(!empty($footer_setting->bottom_address))
                            @foreach(explode("\n", $footer_setting->bottom_address) as $address)
                                <li>{{ trim($address) }}</li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

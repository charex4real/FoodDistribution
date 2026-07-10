<!-- ============Footer Section Starts Here============ -->
@php
    $socials     = getContent('social_icon.element');
    $footer      = getContent('footer_section.content', true);
    $policyPages = getContent('policy_pages.element', false, orderById: true);

@endphp


<!-- Footer Section Starts Here -->
<footer class="footer-section">
    
    <div class="footer-bottom bg-black text-white">
        <div class="container">
            <div class="footer-bottom-wrapper">
                <p class="copy-text">&copy; @lang('All Right Reserved By') <a href="{{ route('home') }}">{{ __(gs('site_name')) }}</a></p>
                <ul class="social-icons">
                    @foreach ($socials as $social)
                        <li>
                            <a href="{{ @$social->data_values->url }}" title="{{ @$social->data_values->title }}" target="_blank">
                                @php echo @$social->data_values->social_icon; @endphp
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</footer>

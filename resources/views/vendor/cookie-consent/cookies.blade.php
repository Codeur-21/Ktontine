<aside id="cookies-policy" class="cookies cookies--no-js" aria-labelledby="kt-cookie-title" data-text="{{ json_encode(__('cookieConsent::cookies.details')) }}">
    <div class="cookies__alert">
        <div class="cookies__container">
            <div class="cookies__wrapper">
                <div class="kt-cookie-brand"><span class="kt-cookie-symbol" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 12a9 9 0 1 1-9-9 4 4 0 0 0 4 4 4 4 0 0 0 5 5Z"/><path d="M8 8h.01M7 14h.01M12 17h.01M14 11h.01" stroke-width="3" stroke-linecap="round"/></svg></span><span>KTONTINE<small>VOTRE CONFIDENTIALITÉ</small></span></div>
                <h2 id="kt-cookie-title" class="cookies__title">@lang('cookieConsent::cookies.title')</h2>
                <div class="cookies__intro">
                    <p>@lang('cookieConsent::cookies.intro')</p>
                    @if($policy)
                        <p>@lang('cookieConsent::cookies.link', ['url' => $policy])</p>
                    @endif
                </div>
                <p class="kt-cookie-reassurance"><span aria-hidden="true">✓</span> Votre choix est mémorisé et peut être modifié à tout moment.</p>
                <div class="cookies__actions">
                    @cookieconsentbutton(action: 'accept.essentials', label: __('cookieConsent::cookies.essentials'), attributes: ['class' => 'cookiesBtn cookiesBtn--essentials'])
                    @cookieconsentbutton(action: 'accept.all', label: __('cookieConsent::cookies.all'), attributes: ['class' => 'cookiesBtn cookiesBtn--accept'])
                </div>
            </div>
        </div>
            <a href="#cookies-policy-customize" class="cookies__btn cookies__btn--customize">
                <span>@lang('cookieConsent::cookies.customize')</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M14.7559 11.9782C15.0814 11.6527 15.0814 11.1251 14.7559 10.7996L10.5893 6.63297C10.433 6.47669 10.221 6.3889 10 6.38889C9.77899 6.38889 9.56703 6.47669 9.41075 6.63297L5.24408 10.7996C4.91864 11.1251 4.91864 11.6527 5.24408 11.9782C5.56951 12.3036 6.09715 12.3036 6.42259 11.9782L10 8.40074L13.5774 11.9782C13.9028 12.3036 14.4305 12.3036 14.7559 11.9782Z" fill="#2C2E30"/>
                </svg>
            </a>
        <div class="cookies__expandable cookies__expandable--custom" id="cookies-policy-customize">
            <form action="{{ route('cookieconsent.accept.configuration') }}" method="post" class="cookies__customize">
                @csrf
            <div class="kt-cookie-settings-heading"><strong>Vos préférences, en toute clarté.</strong><p>Les cookies nécessaires restent actifs pour protéger votre connexion et vos formulaires.</p></div>
                <div class="cookies__sections">
                    @foreach($cookies->getCategories() as $category)
                    <div class="cookies__section">
                        <label for="cookies-policy-check-{{ $category->key() }}" class="cookies__category">
                            @if ($category->key() === 'essentials')
                                <input type="hidden" name="categories[]" value="{{ $category->key() }}" />
                                <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-policy-check-{{ $category->key() }}" checked="checked" disabled="disabled" />
                            @else
                                <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-policy-check-{{ $category->key() }}" />
                            @endif
                            <span class="cookies__box">
                                <strong class="cookies__label">{{ $category->title }}</strong>
                                @if ($category->key() === 'essentials')<small class="kt-cookie-required">Toujours actifs</small>@endif
                            </span>
                            @if($category->description)
                                <p class="cookies__info">{{ $category->description }}</p>
                            @endif
                        </label>

                        <div class="cookies__expandable" id="cookies-policy-{{ $category->key() }}">
                            <ul class="cookies__definitions">
                                @foreach($category->getCookies() as $cookie)
                                <li class="cookies__cookie">
                                    <p class="cookies__name">{{ $cookie->name }}</p>
                                    <p class="cookies__duration">{{ Carbon\Carbon::now()->diffForHumans(Carbon\Carbon::now()->addMinutes($cookie->duration), true) }}</p>
                                    @if($cookie->description)
                                        <p class="cookies__description">{{ $cookie->description }}</p>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="#cookies-policy-{{ $category->key() }}" class="cookies__details">@lang('cookieConsent::cookies.details.more')</a>
                    </div>
                    @endforeach
                </div>
                <div class="cookies__save">
                    <button type="submit" class="cookiesBtn__link">@lang('cookieConsent::cookies.save')</button>
                </div>
            </form>
        </div>
    </div>
</aside>

{{-- STYLES & SCRIPT : feel free to remove them and add your own --}}

<script data-cookie-consent>
    {!! file_get_contents(LCC_ROOT . '/dist/script.js') !!}
</script>
<style data-cookie-consent>
    {!! file_get_contents(LCC_ROOT . '/dist/style.css') !!}
</style>

<style data-cookie-consent>
#cookies-policy.cookies{right:auto;left:24px;bottom:24px;width:460px;max-width:calc(100vw - 48px);max-height:calc(100dvh - 48px);font-family:Arial,Helvetica,sans-serif;scrollbar-width:thin;scrollbar-color:#d9bdab transparent;border-radius:22px;box-shadow:0 18px 70px #29180e26}
#cookies-policy,#cookies-policy *{box-sizing:border-box}
#cookies-policy .cookies__alert{width:100%;max-width:100%;max-height:none;margin:0;border:1px solid #e6d7cb;border-radius:22px;background:#fffdfb;overflow:hidden;box-shadow:none}
#cookies-policy .cookies__wrapper{padding:25px 26px 22px;background:radial-gradient(ellipse at top right,#fff0df,transparent 65%)}
#cookies-policy .kt-cookie-brand{display:flex;align-items:center;gap:12px;color:#36251b;font-size:12px;font-weight:800;letter-spacing:1.3px;margin-bottom:20px}
#cookies-policy .kt-cookie-brand small{display:block;color:#9a6f50;font-size:9px;font-weight:600;letter-spacing:1.7px;margin-top:6px}
#cookies-policy .kt-cookie-symbol{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#2a1d14;color:#ffab65;box-shadow:0 4px 0 #c64b08}
#cookies-policy .cookies__title{font-size:23px;line-height:1.25;letter-spacing:-.6px;color:#2c2119;margin:0 0 12px;font-weight:800}
#cookies-policy .cookies__intro{font-size:13px;line-height:1.8;color:#756252;font-weight:400}
#cookies-policy .cookies__intro p{margin:0 0 10px}
#cookies-policy .cookies__intro a{color:#a3410d;text-underline-offset:3px}
#cookies-policy .kt-cookie-reassurance{display:flex;gap:8px;color:#846f5e;font-size:11px;line-height:1.7;margin:15px 0 0}
#cookies-policy .kt-cookie-reassurance span{color:#a74c11;font-weight:800}
#cookies-policy .cookies__actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:22px}
#cookies-policy .cookiesBtn{margin:0;width:100%}
#cookies-policy .cookiesBtn__link{min-height:45px;padding:12px 10px;border-radius:10px;font-size:12px;line-height:1.4;background:#c64b08;border:1px solid #c64b08;color:#fff;white-space:normal;transition:background .2s,box-shadow .2s}
#cookies-policy .cookiesBtn--essentials .cookiesBtn__link{background:#fff;border-color:#d8c5b4;color:#483226}
#cookies-policy .cookiesBtn__link:hover{opacity:1;background:#a53b06;box-shadow:0 3px 12px #c64b081c}
#cookies-policy .cookiesBtn--essentials .cookiesBtn__link:hover{background:#f7ede4}
#cookies-policy .cookies__btn--customize{background:#f7f2ed;border-color:#eadfd5;padding:17px 26px;color:#65432c;font-size:12px;line-height:1.4}
#cookies-policy.cookies--show .cookies__btn--customize{border-bottom-color:#eadfd5;background:#2a1d14;color:#fff0e4}
#cookies-policy .cookies__btn--customize svg{color:inherit;flex-shrink:0}
#cookies-policy .cookies__btn--customize svg path{fill:currentColor}
#cookies-policy .kt-cookie-settings-heading{padding:22px 26px 0}
#cookies-policy .kt-cookie-settings-heading strong{font-size:16px;color:#332318}
#cookies-policy .kt-cookie-settings-heading p{font-size:12px;line-height:1.7;color:#806b5a;margin:8px 0 12px}
#cookies-policy .cookies__sections{max-height:42dvh;overflow-y:auto;overscroll-behavior:contain}
#cookies-policy .cookies__section{padding:0 26px 7px}
#cookies-policy .cookies__section+.cookies__section{border-top-color:#eee1d5}
#cookies-policy .cookies__label{font-size:13px;color:#3b291c}
#cookies-policy .cookies__info{font-size:12px;color:#806b5a;line-height:1.75;margin:0 0 12px}
#cookies-policy .kt-cookie-required{display:block;color:#9c5a2d;font-size:10px;margin-top:5px}
#cookies-policy .cookies__category input:checked+.cookies__box:after{background:#c64b08}
#cookies-policy .cookies__category input:focus-visible+.cookies__box{outline:2px solid #c64b08;outline-offset:-2px;border-radius:6px}
#cookies-policy .cookies__details{color:#a7440d;font-size:11px;text-decoration:underline;text-underline-offset:3px;margin:10px 0}
#cookies-policy .cookies__definitions{list-style:none;padding:12px 0;margin:0;font-size:11px;line-height:1.7}
#cookies-policy .cookies__name{font-size:11px;color:#6c4930;overflow-wrap:anywhere}
#cookies-policy .cookies__duration{font-size:10px;color:#8e7765;margin-left:8px}
#cookies-policy .cookies__description{color:#806b5a;line-height:1.7;padding-top:5px}
#cookies-policy .cookies__save{padding:16px 26px 20px;background:#fff9f4;border-color:#eadfd5;margin-top:8px}
#cookies-policy :is(a,button):focus-visible{outline:3px solid #ed9d60;outline-offset:3px}
@media(max-width:540px){#cookies-policy.cookies{left:12px;bottom:max(12px,env(safe-area-inset-bottom));max-width:calc(100vw - 24px);width:auto;right:12px;max-height:calc(100dvh - 24px);border-radius:18px}#cookies-policy .cookies__alert{border-radius:18px}#cookies-policy .cookies__wrapper{padding:21px 20px 18px}#cookies-policy .cookies__title{font-size:21px}#cookies-policy .cookies__btn--customize,#cookies-policy .cookies__save{padding-left:20px;padding-right:20px}#cookies-policy .cookies__section{padding-left:20px;padding-right:20px}#cookies-policy .kt-cookie-settings-heading{padding-left:20px;padding-right:20px}#cookies-policy .cookies__actions{gap:8px}}
@media(prefers-reduced-motion:reduce){#cookies-policy.cookies,#cookies-policy .cookies__expandable,#cookies-policy .cookies__container,#cookies-policy .cookiesBtn__link{transition:none}}
</style>

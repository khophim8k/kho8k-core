<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
    {!! setting('site_meta_head_tags', '') !!}
    <meta http-equiv="content-language" content="{{ config('app.locale') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="fb:app_id" content="{{ setting('social_facebook_app_id') }}" />
    <link rel="shortcut icon" href="{{ setting('site_meta_shortcut_icon') }}" type="image/png" />
    {!! SEO::generate() !!}
    @stack('header')
    {!! get_theme_option('additional_css') !!}
    {!! get_theme_option('additional_header_js') !!}
    <script>
        // Chặn F12 và chuột phải
        document.addEventListener('keydown', function(event) {
            if (
                event.key === 'F12' || // F12
                (event.ctrlKey && event.key === 'u') || // Ctrl + U
                (event.ctrlKey && event.shiftKey && event.key === 'i') || // Ctrl + Shift + I
                (event.ctrlKey && event.shiftKey && event.key === 'j') // Ctrl + Shift + J
            ) {
                event.preventDefault();

                window.location.href = '/'; // Chuyển hướng về trang chủ (tuỳ chọn)
            }
        });

        document.addEventListener('contextmenu', function(event) {
            event.preventDefault(); // Chặn menu chuột phải
        });

        // Gọi hàm kiểm tra DevTools khi trang tải
    </script>



    @if (Request::is('kichi'))
        <script>
            const adminRoutePrefix = "{{ config('backpack.base.route_prefix') }}";
            document.addEventListener('keydown', function(event) {
                // Kiểm tra tổ hợp phím Shift + B
                if (event.shiftKey && event.key === 'B') {
                    // Thay thế nội dung trong thẻ body
                    document.body.innerHTML = `
                <div id="admin-container" style="display: block; width: 100%; height: 100vh;">
                    <iframe src="/${adminRoutePrefix}" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            `;
                }
            });
        </script>
    @endif
</head>

<body {!! get_theme_option('body_attributes', '') !!}>

    @yield('body')
    {!! get_theme_option('additional_body_js') !!}

    @yield('footer')
    @stack('scripts')
    {!! get_theme_option('additional_footer_js') !!}
</body>
<script>
    const popupClosed = sessionStorage.getItem("popupClosed");
    const adsPopup = document.querySelector(".ads_popup");
    const adsCatfish = document.querySelector(".ads_catfish");
    // Nếu popup chưa bị đóng, hiển thị popup
    if (popupClosed === null) {
        if (adsPopup) {
            adsPopup.style.display = "block"; // Hiển thị popup
        }
        if (adsCatfish) {
            adsCatfish.style.display = "block";
        }
    } else {
        if (adsPopup) {
            adsPopup.style.display = "none"; // Hiển thị popup
        }
        if (adsCatfish) {
            adsCatfish.style.display = "none";
        }

    }
</script>
</html>

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
        // Nếu popup chưa bị đóng, hiển thị popup


           if (popupClosed=='true') {
               document.querySelector(".ads_popup").style.display = "none"; // ẩn popup
               document.querySelector(".ads_catfish").style.display = "none";
           } else {
               document.querySelector(".ads_popup").style.display = "block"; // Hiển thị popup
               document.querySelector(".ads_catfish").style.display = "block";
           }

           if (document.querySelector(".ads_popup") || document.querySelector(".ads_catfish")) {
       document
           .querySelector(".banner-preload-close")
           .addEventListener("click", function () {
               // Khi người dùng đóng popup, lưu thời gian đóng vào sessionStorage
               sessionStorage.setItem("popupClosed", "true");
               document.querySelector(".ads_popup").style.display = "none";
           });
           document
           .querySelector(".catfish-bottom-close")
           .addEventListener("click", function () {
               sessionStorage.setItem("popupClosed", "true");
               document.querySelector(".ads_catfish").style.display = "none";
           });
       document
           .querySelector(".banner_popup")
           .addEventListener("click", function () {
               sessionStorage.setItem("popupClosed", "true");
               document.querySelector(".ads_popup").style.display = "none";
           });
       }
   </script>
</html>

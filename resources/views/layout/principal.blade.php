<!DOCTYPE HTML>
<html>
	<head>
		<title>@yield('template_title')</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

        <link rel="stylesheet" href="{{ asset('css/layouts/main.css') }}" />

		<noscript><link rel="stylesheet" href="{{ asset('css/layouts/noscript.css') }}"/></noscript>

        <link rel="icon" href="{{ asset('images/logos/g_of_gabu_512x512.png') }}" type="image/png" sizes="512x512">

        <meta property="og:locale" content="es_SV" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Gabu App" />
        <meta property="og:description" content="Únete a GABU y hagamos una revolución del consumo responsable" />
        <meta property="og:url" content="https://server.gabu.app" />
        <meta property="og:site_name" content="Gabu App" />
        <meta property="og:image" content="{{ asset('images/logos/gabu_multicolor_1684x1684.jpg') }}" />
        <meta property="og:image:width" content="1684" />
        <meta property="og:image:height" content="899" />
        <meta name="twitter:card" content="summary_large_image" />



        {{-- CSRF Token --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!--Para sobreescribir el css-->
        @yield('template_css')
	</head>
	<body class="is-preload">

		<!-- Page Wrapper -->
			<div id="page-wrapper">

				<!-- Banner -->
					<section id="banner">
						<div class="inner">
							<div class="logo">
								<img src="{{ asset('images/logos/gabu_multicolor_1684x899.png') }}" alt="Logo Gabu App">
							</div>
							<h2>Gabu App</h2>
							<p>Experiencia Completa. Una fracción del costo.</p>
						</div>
					</section>

                    @yield('template_body')

				<!-- Footer -->
					<section id="footer">
						<div class="inner">
							<h2 class="major">Contáctanos</h2>
							<p>¡Permitimos que un comercio salve el mundo!</p>
                            <p>Únete a GABU y hagamos una revolución del consumo responsable.</p>
							<ul class="contact">
								<li class="icon solid fa-envelope"><a href="mailto:hola@gabu.app?Subject=Contacto%20desde%20Gabu&Body=Hola,%20soy%20%0D%0A%0D%0AEstoy%20interesado%20en...%0D%0A%0D%0A%20" target="_blank"><span class="text-icon">hola@gabu.app</span></a></li>
								<li class="icon brands fa-instagram"><a href="https://www.instagram.com/gabu.app" target="_blank"><span class="text-icon">@gabu.app</span></a></li>
                                <li class="icon brands fa-facebook-f"><a href="https://www.facebook.com/Gabu-103000355519903" target="_blank"><span class="text-icon">Gabu App</span></a></li>
							</ul>
							<ul class="contact">
								<!--
                                <li class="icon solid fa-home">
									<span class="text-icon">Untitled Inc<br />
									1234 Somewhere Road Suite #2894<br />
									Nashville, TN 00000-0000</span>
								</li>

								<li class="icon solid fa-phone"><span class="text-icon">(000) 000-0000</span></li>
                                <li class="icon brands fa-twitter"><a href="#"><span class="text-icon">twitter.com/untitled-tld</span></a></li>
                                -->
							</ul>
							<ul class="copyright">
								<li>Gabu App | ©2021 Todos los derechos reservados</li>
							</ul>
						</div>
					</section>

			</div>

		<!-- Scripts -->

            <script src="{{ asset('js/layouts/jquery.min.js') }}"></script>
			<script src="{{ asset('js/layouts/jquery.scrollex.min.js') }}"></script>
			<script src="{{ asset('js/layouts/browser.min.js') }}"></script>
			<script src="{{ asset('js/layouts/breakpoints.min.js') }}"></script>
			<script src="{{ asset('js/layouts/util.js') }}"></script>
			<script src="{{ asset('js/layouts/main.js') }}"></script>
            @yield('template_script')

	</body>
</html>

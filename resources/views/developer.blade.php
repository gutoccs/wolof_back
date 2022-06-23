@extends('layout.principal')


@section('template_title')
Políticas de Privacidad - Gabu App
@endsection


@section('template_css')

    <style>
        #body-content {
            padding: 1em;
            margin-bottom: 70px;
        }

        #body-content .inner {
            margin: 0 auto;
            max-width: 55em;
            text-align: justify;
        }

        .link {
            border-bottom: none;
        }


    </style>

@endsection


@section('template_body')

    <section id="body-content">
        <div class="inner">
            <h2>Desarrollado por:</h2>

            <p><strong>Gustavo Escobar Cobos</strong></p>
            <p><strong>Backend:</strong> PHP/Laravel</p>
            <p><strong>Mobile:</strong> Flutter</p>

            <a class="link" href="https://linkedin.com/in/gutoccs" target="_blank">
                <button>Linkedin</button>
            </a>

            <a class="link" href="https://github.com/gutoccs" target="_blank">
                <button class="link">GitHub</button>
            </a>

        </div>

    </section>


@endsection



@section('template_script')

@endsection

@extends('layout.principal')


@section('template_title')
Gabu App
@endsection


@section('template_css')

    <style>
        #body-error {
            padding: 1em;
        }

        #body-error .inner {
            margin: 0 auto;
            max-width: 55em;
            text-align: center;
        }

        #text-error {
            font-size: 2em;
            color: #FF4338;
        }

        #link-error {
            font-size: 2em;
            color: #00B3E3;
        }
    </style>

@endsection


@section('template_body')

    <section id="body-error">
        <div class="inner">
            <h1 id="text-error">¡Oops! Creeemos que te has perdido</h2>
            <a id="link-error" href="/">volver</a>
        </div>

    </section>

@endsection



@section('template_script')

@endsection

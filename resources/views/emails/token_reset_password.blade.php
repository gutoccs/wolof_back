@extends('emails.layouts.app')


@section('template_title')
Gabu App
@endsection


@section('template_css')

    <style>

    </style>

@endsection




@section('template_text')

    <tr>
        <td bgcolor="#ffffff" align="center" valign="top" style="padding: 5px 20px 5px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
            <h1 style="font-size: 24px; font-weight: 400; margin: 1;">¡Hola, {{ $fullNameClient }}!</h1>
        </td>
    </tr>

    <tr>
        <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
            <p style="margin: 0;">Código de Recuperación:</p>
        </td>
    </tr>

    <tr>
        <td bgcolor="#ffffff" align="left">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center" style="border-radius: 3px;" bgcolor="#FFC843">
                                    <p style="font-size: 24px; font-family: Helvetica, Arial, sans-serif; text-decoration: none; color: #002F32; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFC843; display: inline-block; letter-spacing: 5px; font-weight: bold;">{{ $token }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td bgcolor="#ffffff" align="left" style="padding: 0px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
            <p style="margin: 0;">Ignore este mensaje si usted no realizó esta solicitud, así su contraseña permanecerá igual</p>
        </td>
    </tr>

@endsection


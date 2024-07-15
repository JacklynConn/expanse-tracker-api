<x-mail::message>
<p>Hi {{ $user->name }},</p>
<p>
    We received a request to verify your account on {{ config('app.name') }} through your e-mail address. Your verification code is:
</p>
<p>
    <strong>{{ $code }}</strong>
</p>
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

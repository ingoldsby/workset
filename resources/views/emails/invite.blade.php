<x-mail::message>
# You've been invited to Workset

{{ $inviterName }} has invited you to join **Workset** as a **{{ $role }}**.

@if(isset($ptName))
Your assigned personal trainer will be **{{ $ptName }}**.
@endif

To accept this invitation and create your account, please click the button below:

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

This invitation will expire on **{{ $expiresAt }}**.

If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

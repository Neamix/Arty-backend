@php
    use Modules\UserManagement\Enums\OtpUsage;

    $copy = match ($usage) {
        OtpUsage::PasswordReset => [
            'eyebrow' => 'Account security',
            'title' => 'Reset your password',
            'heroSub' => 'Use the verification code below to set a new password. It keeps your pipeline — and every lead in it — yours alone.',
            'intro' => 'We received a request to reset your password. Enter this code in the open tab to continue.',
            'note' => 'You can safely ignore this email — your password won’t change until someone enters the code above.',
        ],
        OtpUsage::LoginVerification => [
            'eyebrow' => 'Login verification',
            'title' => 'Verify your login',
            'heroSub' => 'Use the verification code below to confirm it’s really you signing in.',
            'intro' => 'Enter this code in the open tab to finish signing in.',
            'note' => 'If you didn’t try to sign in, you can safely ignore this email.',
        ],
        OtpUsage::EmailVerification => [
            'eyebrow' => 'Email verification',
            'title' => 'Verify your email',
            'heroSub' => 'Use the verification code below to confirm your email and finish setting up your Artmes account.',
            'intro' => 'Enter this code in the open tab to verify your email address and continue.',
            'note' => 'If you didn’t create an Artmes account, you can safely ignore this email.',
        ],
    };

    $digits = str_split($otp);
@endphp
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="x-apple-disable-message-reformatting" />
  <meta name="color-scheme" content="light only" />
  <meta name="supported-color-schemes" content="light only" />
  <title>{{ $copy['title'] }}</title>
  <!--[if mso]>
  <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
  <![endif]-->
  <style>
    /* Progressive enhancement only — Gmail ignores most of this, so every
       critical style is ALSO inlined below. Safe to keep. */
    @media only screen and (max-width:600px){
      .container { width:100% !important; }
      .px { padding-left:24px !important; padding-right:24px !important; }
      .otp-cell { width:42px !important; height:54px !important; font-size:26px !important; }
      .h1 { font-size:30px !important; }
    }
  </style>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#EDEAE0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

  <!-- preheader (hidden) -->
  <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; opacity:0; font-size:1px; line-height:1px; color:#EDEAE0;">
    Your Artmes verification code is {{ $otp }} — it expires in {{ $expiresInMinutes }} minutes.
    &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#EDEAE0;">
    <tr>
      <td align="center" style="padding:32px 12px;">

        <!-- container -->
        <table role="presentation" class="container" width="560" cellpadding="0" cellspacing="0" border="0" style="width:560px; max-width:560px;">

          <!-- eyebrow -->
          <tr>
            <td class="px" align="right" style="padding:4px 8px 18px 8px; font-family:Helvetica, Arial, sans-serif; font-size:12px; font-weight:600; letter-spacing:1.4px; text-transform:uppercase; color:#6B7080;">
              {{ $copy['eyebrow'] }}
            </td>
          </tr>

          <!-- card -->
          <tr>
            <td>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF; border:1px solid #E8E2D3; border-radius:20px;">

                <!-- dark hero -->
                <tr>
                  <td class="px" style="background-color:#0A0B0E; border-radius:20px 20px 0 0; padding:32px 40px 30px 40px;">
                    <div style="font-family:Helvetica, Arial, sans-serif; font-size:22px; font-weight:bold; letter-spacing:-0.5px; color:#B9FF3D; margin:0 0 26px 0;">Artmes</div>
                    <h1 class="h1" style="margin:0 0 8px 0; font-family:Helvetica, Arial, sans-serif; font-size:34px; line-height:1.05; letter-spacing:-1px; color:#F5F2EA; font-weight:bold;">
                      {{ $copy['title'] }}
                    </h1>
                    <p style="margin:0; font-family:Helvetica, Arial, sans-serif; font-size:15px; line-height:1.55; color:#B7B4AC;">
                      {{ $copy['heroSub'] }}
                    </p>
                  </td>
                </tr>

                <!-- intro -->
                <tr>
                  <td class="px" style="padding:32px 40px 8px 40px; font-family:Helvetica, Arial, sans-serif;">
                    <p style="margin:0 0 24px 0; font-size:15px; line-height:1.55; color:#3B3F49;">
                      {{ $copy['intro'] }}
                    </p>
                  </td>
                </tr>

                <!-- OTP -->
                <tr>
                  <td class="px" style="padding:0 40px 8px 40px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                      <tr>
                        @foreach ($digits as $i => $digit)
                          @if ($i > 0)
                          <td style="width:8px;">&nbsp;</td>
                          @endif
                          <td class="otp-cell" style="font-family:'Courier New', Courier, monospace; font-size:34px; font-weight:bold; color:#0A0B0E; background-color:#F4F1E6; border:1px solid #E2DCCB; border-radius:12px; width:52px; height:64px; text-align:center; vertical-align:middle;">{{ $digit }}</td>
                        @endforeach
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- expiry pill -->
                <tr>
                  <td align="center" style="padding:18px 40px 4px 40px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="background-color:#F4F1E6; border-radius:999px; padding:7px 16px; font-family:Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; color:#5B5F2A;">
                          Expires in {{ $expiresInMinutes }} minutes
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- divider -->
                <tr>
                  <td class="px" style="padding:28px 40px 0 40px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr><td style="height:1px; background-color:#ECE6D7; line-height:1px; font-size:1px;">&nbsp;</td></tr>
                    </table>
                  </td>
                </tr>

                <!-- security note -->
                <tr>
                  <td class="px" style="padding:26px 40px 36px 40px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F1; border:1px solid #EFE9DA; border-radius:14px;">
                      <tr>
                        <td style="padding:16px 18px; font-family:Helvetica, Arial, sans-serif; font-size:13px; line-height:1.6; color:#6B7080;">
                          <span style="color:#0A0B0E; font-weight:bold;">Didn’t request this?</span>
                          {{ $copy['note'] }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

              </table>
            </td>
          </tr>

          <!-- footer -->
          <tr>
            <td class="px" align="center" style="padding:26px 40px 10px 40px; font-family:Helvetica, Arial, sans-serif;">
              <p style="margin:0 0 10px 0; font-size:12px; line-height:1.5; color:#9A9687;">
                Sent by Artmes · One pipeline for every lead.
              </p>
              <p style="margin:14px 0 0 0; font-size:11px; color:#B3AFA2;">© {{ date('Y') }} Artmes, Inc.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>

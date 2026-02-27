<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - ANJO Wholesale</title>
    <style>
        /* Reset styles for email clients */
        body, table, td, a { text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }

        /* Mobile Styles */
        @media screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .button { width: 100% !important; text-align: center !important; }
        }
    </style>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px; border-bottom: 1px solid #eeeeee;">
                            <img src="{{ asset('assets/images/aw-log.svg') }}" alt="ANJO Wholesale" width="180" style="display: block; width: 180px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="margin: 0 0 20px 0; color: #002D5B; font-size: 24px; font-weight: 700; line-height: 30px; text-align: center;">Verify Your Email Address</h1>
                            
                            <p style="margin: 0 0 20px 0; color: #444444; font-size: 16px; line-height: 24px;">
                                Hello **{{ $user->name }}**,
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #444444; font-size: 16px; line-height: 24px;">
                                Thank you for joining <strong> ANJO Wholesale </strong> . To complete your registration and secure your account, please verify your email address by clicking the button below:
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 30px;">
                                        <a href="{{ route('verification.verify', ['token' => $token]) }}" target="_blank" style="background-color: #002D5B; border-radius: 4px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: bold; line-height: 50px; text-align: center; text-decoration: none; width: 250px; -webkit-text-size-adjust: none;">Verify My Email</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px 0; color: #777777; font-size: 14px; line-height: 21px; text-align: center;">
                                <i>Note: This link will expire in <strong>30 minutes</strong>.</i>
                            </p>
                            
                            <p style="margin: 0; color: #999999; font-size: 13px; line-height: 20px; text-align: center; border-top: 1px solid #eeeeee; padding-top: 20px;">
                                If you did not create an account with us, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px; color: #aaaaaa; font-size: 12px; line-height: 18px;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} ANJO Wholesale. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
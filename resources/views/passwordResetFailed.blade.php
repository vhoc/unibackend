<!doctype html>
<html xmlns:mso="urn:schemas-microsoft-com:office:office" xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">
    <head>
    <meta charset="UTF-8">
    <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Use the latest (edge) version of IE rendering engine -->
    <title>Error</title>
    <!-- The title tag shows in email notifications, like Android 4.4. -->
    <!-- Please use an inliner tool to convert all CSS to inline as inpage or external CSS is removed by email clients -->
    <!-- important in CSS is used to prevent the styles of currently inline CSS from overriding the ones mentioned in media queries when corresponding screen sizes are encountered -->

    <!-- CSS Reset -->
    <style type="text/css">
/* What it does: Remove spaces around the email design added by some email clients. */
      /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
html,  body { 
	margin: 0 !important;
	padding: 0 !important;
	height: 100% !important;
	width: 100% !important;
}
/* What it does: Stops email clients resizing small text. */
* {
	-ms-text-size-adjust: 100%;
	-webkit-text-size-adjust: 100%;
}
/* What it does: Forces Outlook.com to display emails full width. */
.ExternalClass {
	width: 100%;
}
/* What is does: Centers email on Android 4.4 */
div[style*="margin: 16px 0"] {
	margin: 0 !important;
}
/* What it does: Stops Outlook from adding extra spacing to tables. */
table,  td {
	mso-table-lspace: 0pt !important;
	mso-table-rspace: 0pt !important;
}
/* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
table {
	border-spacing: 0 !important;
	border-collapse: collapse !important;
	table-layout: fixed !important;
	margin: 0 auto !important;
}
table table table {
	table-layout: auto;
}
/* What it does: Uses a better rendering method when resizing images in IE. */
img {
	-ms-interpolation-mode: bicubic;
}
/* What it does: Overrides styles added when Yahoo's auto-senses a link. */
.yshortcuts a {
	border-bottom: none !important;
}
/* What it does: Another work-around for iOS meddling in triggered links. */
a[x-apple-data-detectors] {
	color: inherit !important;
}
</style>

    <!-- Progressive Enhancements -->
    <style type="text/css">
        
        /* What it does: Hover styles for buttons */
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        .button-td:hover,
        .button-a:hover {
            background: #555555 !important;
            border-color: #555555 !important;
        }

        /* Media Queries */
        @media screen and (max-width: 600px) {

            .email-container {
                width: 100% !important;
            }

            /* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
            .fluid,
            .fluid-centered {
                max-width: 100% !important;
                height: auto !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            /* And center justify these ones. */
            .fluid-centered {
                margin-left: auto !important;
                margin-right: auto !important;
            }

            /* What it does: Forces table cells into full-width rows. */
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            /* And center justify these ones. */
            .stack-column-center {
                text-align: center !important;
            }
        
            /* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
                float: none !important;
            }
            table.center-on-narrow {
                display: inline-block !important;
            }
                
        }

    </style>
    
<!--[if gte mso 9]><xml>
<mso:CustomDocumentProperties>
<mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Editor msdt:dt="string">Service SPOMigration</mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Editor>
<mso:Order msdt:dt="string">100.000000000000</mso:Order>
<mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Author msdt:dt="string">Service SPOMigration</mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Author>
<mso:ContentTypeId msdt:dt="string">0x010100DC84F830153980469374CCE9391524B6</mso:ContentTypeId>
</mso:CustomDocumentProperties>
</xml><![endif]-->
</head>
    <body bgcolor="#222222" width="100%" style="margin: 0;" yahoo="yahoo">
    <table bgcolor="#eaeced" cellpadding="0" cellspacing="0" border="0" height="100%" width="100%" style="border-collapse:collapse;">
      <tr>
        <td><center style="width: 100%;">
            
            <!-- Visually Hidden Preheader Text : BEGIN -->
            <div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">  </div>
            <!-- Visually Hidden Preheader Text : END -->
          <!-- Email Header : BEGIN -->
          <table align="center" width="600" class="email-container" style="background-color: #ffffff;">
            <!-- Thumbnail Left, Text Right : BEGIN -->
            <tr>
                <td dir="ltr" align="center" valign="top" width="100%" style="padding: 10px;"><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                    <td width="50%" class="stack-column-center" valign="top"><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" >
                        <tr>
                        <td dir="ltr" valign="top" style="padding: 0 10px;"><img src="{{ env("APP_LOGO_URL") }}" alt="alt_text" border="0" class="center-on-narrow"></td>
                      </tr>
                      </table>
					</td>
                    
					<td width="50%" class="stack-column-center"><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                        <td dir="ltr" valign="top" style="font-family: sans-serif; font-size: 16px; font-weight: bold; mso-height-rule: exactly; color: {{ env("PRIMARY_COLOR") }}; padding: 10px; text-align: right;" class="center-on-narrow">ERROR
                        </td>
                      </tr>
                      </table>
					</td>
                  </tr>
                  </table></td>	
              </tr>
            <!-- Thumbnail Left, Text Right : END -->
			
            </table>
          <!-- Email Body : BEGIN -->
            <table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" width="600" class="email-container">
            <!-- 1 Column Text : Intro -->
            <tr>
                <td style="padding: 30px; text-align: left; font-family: sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 28px; color: #555555;">
					Hola,<br><br>
						<strong>Tu contraseña no pudo ser restablecida</strong>. Probablemente se deba a que el enlace expiró, o ya fue utilizado anteriormente. Intenta iniciar de nuevo el proceso de restablecimiento desde la App. Si el problema persiste, puedes contactarnos al correo de abajo para dar seguimiento al problema.
					<br><br>
				</td>
			</tr>
			<tr>
				<td style="padding: 30px; text-align: center; font-family: sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 28px; color: #555555;">
					<span style="text-align: center; align-content: center"><strong>Estamos para ayudarte</strong><br>
					Si tienes dudas o comentarios acerca de la aplicación, envianos un correo a <a href="mailto:{{ env('SUPPORT_MAIL_ADDRESS') }}" style="text-decoration-color: {{ env("PRIMARY_COLOR") }}; color: {{ env("PRIMARY_COLOR") }}">{{ env('SUPPORT_MAIL_ADDRESS') }}</a>.</span>
				</td>
            </tr>
          	<!-- 1 Column Text : Intro -->
          </table>
          <!-- Email Body : END --> 
          </center></td>
      </tr>
    </table>
</body>
</html>

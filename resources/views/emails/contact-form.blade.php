<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouă cerere de contact</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #6B46C1; margin-top: 0;">Nouă cerere de contact</h1>
        <p style="margin-bottom: 0;">Ai primit o nouă cerere de contact prin formularul de pe site.</p>
    </div>

    <div style="background-color: #ffffff; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;">
        <h2 style="color: #333; margin-top: 0; border-bottom: 2px solid #6B46C1; padding-bottom: 10px;">Detalii contact</h2>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; font-weight: bold; width: 150px;">Nume complet:</td>
                <td style="padding: 10px 0;">{{ $name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Email:</td>
                <td style="padding: 10px 0;">
                    <a href="mailto:{{ $email }}" style="color: #6B46C1; text-decoration: none;">{{ $email }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Telefon:</td>
                <td style="padding: 10px 0;">
                    <a href="tel:{{ $phone }}" style="color: #6B46C1; text-decoration: none;">{{ $phone }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Nume loc de joacă:</td>
                <td style="padding: 10px 0;">{{ $playground_name }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; font-size: 14px; color: #666;">
        <p style="margin: 0;">
            <strong>Notă:</strong> Poți răspunde direct la acest email pentru a contacta persoana care a completat formularul.
        </p>
    </div>

    <div style="margin-top: 20px; text-align: center; color: #999; font-size: 12px;">
        <p>Acest email a fost generat automat de către Hopo Platform.</p>
    </div>
</body>
</html>

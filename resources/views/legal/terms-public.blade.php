<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termeni și Condiții - Hopo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans">

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-sm text-blue-600 underline">&larr; Înapoi</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Termeni și Condiții</h1>
            <p class="text-gray-500 text-sm">Versiunea {{ $version }}</p>
        </div>

        <div class="space-y-6 text-gray-700 text-sm leading-relaxed">
            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">1. Introducere</h2>
                <p>Prin utilizarea serviciilor noastre, acceptați în mod expres și necondiționat următorii termeni și condiții. Acești termeni reglementează accesul și utilizarea serviciilor pentru înregistrarea și gestionarea sesiunilor de joacă ale copiilor minori.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">2. Condiții de Acces</h2>
                <p class="mb-2">Pentru utilizarea serviciilor trebuie să:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Aveți vârsta legală (minimum 18 ani)</li>
                    <li>Furnizați informații corecte și complete</li>
                    <li>Acceptați acești termeni și politica GDPR</li>
                    <li>Respectați regulamentul locației</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">3. Regulamentul Locației</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Copiii trebuie supravegheați de un adult responsabil</li>
                    <li>Nu este permisă utilizarea agresivă sau periculoasă a echipamentelor</li>
                    <li>Este interzisă introducerea alimentelor în zonele de joacă (exceptând zonele desemnate)</li>
                    <li>Copiii cu boli contagioase nu pot accesa serviciile</li>
                    <li>Este obligatorie respectarea liniștii și a celorlalți vizitatori</li>
                    <li>Este interzis fumatul în interior</li>
                    <li>Personalul are dreptul să refuze accesul în caz de nerespectare a regulamentului</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">4. Înregistrarea Copilului</h2>
                <p>La înregistrare veți furniza numele și numărul de telefon. Vă angajați că informațiile sunt corecte și actualizate. Orice modificare trebuie comunicată imediat la recepție.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">5. Responsabilități</h2>
                <p class="mb-2"><strong>Responsabilitățile dumneavoastră:</strong></p>
                <ul class="list-disc list-inside space-y-1 mb-3">
                    <li>Supravegherea corespunzătoare a copilului</li>
                    <li>Comunicarea alergiilor sau condițiilor medicale relevante</li>
                    <li>Respectarea regulamentului și plata serviciilor</li>
                </ul>
                <p class="mb-2"><strong>Responsabilitățile noastre:</strong></p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Oferirea unui mediu sigur conform standardelor</li>
                    <li>Protejarea datelor personale conform GDPR</li>
                    <li>Gestionarea corectă a sesiunilor și facturarea</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">6. Neasumarea Răspunderii</h2>
                <p class="mb-2"><strong>IMPORTANT:</strong> Prin utilizarea serviciilor înțelegeți și acceptați că nu ne asumăm răspunderea pentru:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Accidente sau răniri suferite în timpul utilizării serviciilor</li>
                    <li>Pierderi sau deteriorări ale bunurilor personale</li>
                    <li>Incidente cauzate de nerespectarea regulamentului sau utilizarea necorespunzătoare a echipamentelor</li>
                </ul>
                <p class="mt-2">Utilizarea serviciilor se face pe propria răspundere. Părinții/tutorii sunt singurii responsabili pentru siguranța copiilor.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">7. Modificarea Termenilor</h2>
                <p>Ne rezervăm dreptul de a modifica acești termeni în orice moment. Continuarea utilizării serviciilor după modificare reprezintă acceptarea noilor termeni.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">8. Legea Aplicabilă</h2>
                <p>Acești termeni sunt guvernați de legile României. Orice dispute vor fi rezolvate de instanțele competente din România.</p>
            </section>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-400 text-center">
            Versiunea {{ $version }} &mdash; &copy; {{ date('Y') }} Hopo
        </div>
    </div>
</div>

</body>
</html>

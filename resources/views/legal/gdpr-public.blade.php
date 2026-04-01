<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica GDPR - Hopo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans">

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-sm text-blue-600 underline">&larr; Înapoi</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Politica de Protecție a Datelor cu Caracter Personal</h1>
            <p class="text-gray-500 text-sm">Versiunea {{ $version }} &mdash; Conform Regulamentului (UE) 2016/679 (GDPR)</p>
        </div>

        <div class="space-y-6 text-gray-700 text-sm leading-relaxed">
            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">1. Preambul</h2>
                <p>Respectăm dreptul dumneavoastră la confidențialitate și ne angajăm să protejăm datele cu caracter personal ale dumneavoastră și ale copilului în conformitate cu Regulamentul General privind Protecția Datelor (GDPR) și legislația națională aplicabilă.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">2. Datele Colectate</h2>
                <p class="mb-2">Colectăm următoarele date:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Despre copil:</strong> nume și prenume, cod intern de identificare</li>
                    <li><strong>Despre părinte/tutor:</strong> nume complet, număr de telefon</li>
                    <li><strong>Despre vizite:</strong> data, ora și durata sesiunii</li>
                </ul>
                <p class="mt-2">Nu colectăm date biometrice, date de locație precisă sau date sensibile fără consimțământ explicit.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">3. Scopul Prelucrării</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Gestionarea înregistrărilor și sesiunilor de joacă</li>
                    <li>Identificarea copilului și a părintelui în caz de urgență</li>
                    <li>Facturare și conformitate fiscală</li>
                    <li>Respectarea obligațiilor legale</li>
                </ul>
                <p class="mt-2">Datele nu sunt utilizate pentru marketing direct și nu sunt vândute sau partajate cu terți în scopuri comerciale.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">4. Baza Legală</h2>
                <p>Prelucrarea se bazează pe consimțământul dumneavoastră, executarea serviciului contractat și obligații legale.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">5. Durata Stocării</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Date despre copil și părinte — 3 ani după ultima utilizare</li>
                    <li>Date despre sesiuni — minim 5 ani (conformitate fiscală)</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">6. Drepturile Dumneavoastră</h2>
                <p class="mb-2">Conform GDPR, aveți dreptul la:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Acces la datele prelucrate</li>
                    <li>Rectificarea datelor inexacte</li>
                    <li>Ștergerea datelor („dreptul de a fi uitat")</li>
                    <li>Limitarea prelucrării</li>
                    <li>Portabilitatea datelor</li>
                    <li>Retragerea consimțământului în orice moment</li>
                </ul>
                <p class="mt-2">Pentru exercitarea drepturilor, contactați recepția locației. Aveți de asemenea dreptul să depuneți o plângere la ANSPDCP (www.dataprotection.ro).</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">7. Securitatea Datelor</h2>
                <p>Implementăm măsuri tehnice și organizatorice adecvate pentru protejarea datelor: criptare, acces restricționat, backup-uri regulate și monitorizare continuă.</p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-gray-900 mb-2">8. Date despre Copii Minori</h2>
                <p>Datele despre copii minori sunt prelucrate exclusiv cu consimțământul explicit al părinților/tutorilor legali. Nu colectăm date direct de la copii.</p>
            </section>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-400 text-center">
            Versiunea {{ $version }} &mdash; &copy; {{ date('Y') }} Hopo
        </div>
    </div>
</div>

</body>
</html>

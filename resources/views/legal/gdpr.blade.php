@extends('layouts.app')

@section('title', 'Politica de Protecție a Datelor cu Caracter Personal (GDPR)')

@section('page-title', 'Politica GDPR')

@section('content')
<div class="bg-white rounded-lg shadow p-6 md:p-8 max-w-4xl mx-auto">
    <div class="mb-6 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Politica de Protecție a Datelor cu Caracter Personal</h1>
        <p class="text-gray-600">Versiunea {{ $version }} - Ultima actualizare: {{ date('d.m.Y') }}</p>
        <p class="text-sm text-gray-500 mt-2">Conform Regulamentului (UE) 2016/679 (GDPR)</p>
        <p class="text-sm font-medium text-gray-700 mt-2">{{ $locationName }}</p>
    </div>

    <div class="prose prose-lg max-w-none">
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Preambul</h2>
            <p class="text-gray-700 mb-4">
                {{ $locationName }} respectă dreptul dumneavoastră la confidențialitate și ne angajăm să protejăm datele cu caracter personal ale dumneavoastră și ale copilului în conformitate cu Regulamentul General privind Protecția Datelor (GDPR) și legislația națională aplicabilă.
            </p>
            <p class="text-gray-700">
                Această politică explică cum colectăm, utilizăm, stocăm și protejăm datele cu caracter personal ale părinților/tutorilor legali și ale copiilor minori în contextul utilizării serviciilor {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Operator de Date</h2>
            <p class="text-gray-700 mb-4">
                Operatorul de date este <strong>{{ $locationName }}</strong>, care prelucrează datele cu caracter personal pentru gestionarea sesiunilor de joacă și serviciilor oferite.
            </p>
            <p class="text-gray-700 mb-4">
                Datele cu caracter personal sunt procesate în conformitate cu GDPR și legislația națională aplicabilă pentru protecția datelor cu caracter personal.
            </p>
            <p class="text-gray-700">
                Pentru întrebări privind prelucrarea datelor, vă rugăm să contactați {{ $locationName }} direct.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Datele Colectate</h2>
            
            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">3.1. Date despre Copil</h3>
            <p class="text-gray-700 mb-4">Colectăm următoarele date cu caracter personal despre copil:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Nume și prenume</strong> - pentru identificare și gestionarea sesiunilor</li>
                <li><strong>Data nașterii</strong> - pentru verificarea vârstei și calcularea vârstei</li>
                <li><strong>Alergii și condiții medicale relevante</strong> - pentru asigurarea siguranței copilului (opțional, cu consimțământ explicit)</li>
                <li><strong>Cod intern</strong> - pentru identificare unică în sistemul {{ $locationName }}</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">3.2. Date despre Părinte/Tutor Legal</h3>
            <p class="text-gray-700 mb-4">Colectăm următoarele date cu caracter personal despre dumneavoastră:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Nume complet</strong> - pentru identificare și comunicare</li>
                <li><strong>Număr de telefon</strong> - pentru comunicare urgentă și notificări</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">3.3. Date despre Sesiuni de Joacă</h3>
            <p class="text-gray-700 mb-4">Înregistrăm următoarele informații despre sesiunile de joacă:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Data și ora începerii</strong> - pentru urmărirea timpului</li>
                <li><strong>Data și ora încheierii</strong> - pentru calcularea duratei</li>
                <li><strong>Durata efectivă</strong> - timpul petrecut exclusiv pauzele</li>
                <li><strong>Cod brățară RFID</strong> - pentru identificare</li>
                <li><strong>Costul sesiunii</strong> - pentru facturare</li>
            </ul>

            <p class="text-gray-700 mt-4">
                <strong>Notă:</strong> Nu colectăm date despre locația precisă sau date biometrice. Datele sunt limitate strict la cele necesare pentru oferirea serviciilor {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Scopul Prelucrării</h2>
            <p class="text-gray-700 mb-4">Datele cu caracter personal sunt prelucrate de {{ $locationName }} exclusiv pentru următoarele scopuri:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Gestionarea sesiunilor de joacă</strong> - pentru organizarea și urmărirea timpului petrecut de copil în {{ $locationName }}</li>
                <li><strong>Facturare</strong> - pentru calcularea și emiterea facturilor pentru serviciile utilizate</li>
                <li><strong>Securitate</strong> - pentru asigurarea siguranței copilului și identificarea în caz de urgență</li>
                <li><strong>Comunicare</strong> - pentru contactarea dumneavoastră în caz de necesitate (de exemplu, în caz de urgență)</li>
                <li><strong>Conformitate legală</strong> - pentru respectarea obligațiilor legale și fiscale</li>
            </ul>
            <p class="text-gray-700">
                <strong>{{ $locationName }} nu utilizează datele pentru marketing direct sau pentru partajarea cu terți în scopuri comerciale.</strong>
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Baza Legală pentru Prelucrare</h2>
            <p class="text-gray-700 mb-4">Prelucrarea datelor cu caracter personal de către {{ $locationName }} se bazează pe:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Consimțământul dumneavoastră</strong> - pentru prelucrarea datelor despre copil și despre dumneavoastră</li>
                <li><strong>Executarea unui contract</strong> - pentru gestionarea sesiunilor și facturare</li>
                <li><strong>Interesul legitim</strong> - pentru securitate și prevenirea fraudelor</li>
                <li><strong>Obligații legale</strong> - pentru respectarea cerințelor legale și fiscale aplicabile</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Durata Stocării</h2>
            <p class="text-gray-700 mb-4">{{ $locationName }} stochează datele cu caracter personal pentru următoarele perioade:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>Date despre copil și părinte</strong> - pentru durata utilizării serviciilor și încă 3 ani după încetarea utilizării</li>
                <li><strong>Date despre sesiuni</strong> - pentru durata necesară facturării și conformității fiscale (minim 5 ani conform legislației fiscale)</li>
                <li><strong>Date despre alergii</strong> - până când sunt actualizate sau retrase de către dumneavoastră</li>
            </ul>
            <p class="text-gray-700">
                După expirarea perioadei de stocare, datele vor fi șterse sau anonimizate în mod sigur de către {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Accesarea și Partajarea Datelor</h2>
            <p class="text-gray-700 mb-4">Datele cu caracter personal pot fi accesate de:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Personalul autorizat al {{ $locationName }} (pentru gestionarea sesiunilor și serviciilor)</li>
                <li>Furnizorii de servicii IT (pentru întreținerea sistemului de gestionare, sub acord de confidențialitate strict)</li>
                <li>Autoritățile competente (doar în cazuri prevăzute de lege, la cerere oficială)</li>
            </ul>
            <p class="text-gray-700 mb-4">
                <strong>{{ $locationName }} nu vinde, nu închiriază sau nu partajează datele cu terți pentru scopuri de marketing sau comerciale.</strong>
            </p>
            <p class="text-gray-700">
                Datele pot fi stocate pe servere cloud pentru backup și siguranță, toți furnizorii fiind certificați GDPR și situați în Uniunea Europeană sau în țări cu nivel adecvat de protecție.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Drepturile Dumneavoastră</h2>
            <p class="text-gray-700 mb-4">Conform GDPR, aveți următoarele drepturi în raport cu {{ $locationName }}:</p>
            
            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.1. Dreptul de Acces</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să solicitați accesul la datele cu caracter personal prelucrate despre dumneavoastră și despre copil de către {{ $locationName }}.</p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.2. Dreptul de Rectificare</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să solicitați corectarea datelor inexacte sau incomplete.</p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.3. Dreptul la Ștergere ("Dreptul de a fi uitat")</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să solicitați ștergerea datelor în anumite condiții (de exemplu, când nu mai sunt necesare sau retrageți consimțământul).</p>
            <p class="text-gray-700 mb-4">
                <strong>Notă:</strong> Anumite date pot fi păstrate de {{ $locationName }} pentru respectarea obligațiilor legale (de exemplu, conformitate fiscală).
            </p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.4. Dreptul la Limitarea Prelucrării</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să solicitați limitarea prelucrării în anumite circumstanțe.</p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.5. Dreptul la Portabilitatea Datelor</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să primiți datele într-un format structurat și să le transferați către alt operator.</p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.6. Dreptul de Opoziție</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să vă opuneți prelucrării pentru interes legitim.</p>

            <h3 class="text-xl font-semibold text-gray-800 mb-3 mt-6">8.7. Dreptul de a Vă Retrage Consimțământul</h3>
            <p class="text-gray-700 mb-4">Aveți dreptul să vă retrageți consimțământul în orice moment pentru prelucrarea bazată pe consimțământ.</p>
            <p class="text-gray-700">
                Retragerea consimțământului nu afectează legalitatea prelucrării efectuate înainte de retragere de către {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Exercițiul Drepturilor</h2>
            <p class="text-gray-700 mb-4">
                Pentru a exercita oricare dintre drepturile menționate mai sus, vă rugăm să ne contactați la {{ $locationName }}.
            </p>
            <p class="text-gray-700 mb-4">
                {{ $locationName }} va răspunde la solicitarea dumneavoastră în termen de maximum 30 de zile de la primirea acesteia.
            </p>
            <p class="text-gray-700">
                În cazul în care considerați că prelucrarea datelor de către {{ $locationName }} încalcă GDPR, aveți dreptul să depuneți o plângere la Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal (ANSPDCP).
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Securitatea Datelor</h2>
            <p class="text-gray-700 mb-4">{{ $locationName }} implementează măsuri tehnice și organizatorice pentru protejarea datelor:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Criptare a datelor în tranzit și la repaus</li>
                <li>Acces restricționat la date (doar personal autorizat al {{ $locationName }})</li>
                <li>Autentificare și autorizare pentru accesul la sistem</li>
                <li>Backup-uri regulate și planuri de recuperare</li>
                <li>Monitorizare continuă pentru securitate</li>
                <li>Actualizări periodice de securitate</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Date despre Copii Minori</h2>
            <p class="text-gray-700 mb-4">
                Datele despre copii minori sunt prelucrate de {{ $locationName }} doar cu consimțământul explicit al părinților/tutorilor legali. Nu colectăm date direct de la copii.
            </p>
            <p class="text-gray-700">
                Datele despre alergii și condiții medicale sunt procesate doar cu consimțământul explicit al dumneavoastră și sunt utilizate exclusiv de {{ $locationName }} pentru asigurarea siguranței copilului.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Modificări ale Politicii</h2>
            <p class="text-gray-700 mb-4">
                {{ $locationName }} își rezervă dreptul de a modifica această politică pentru a reflecta schimbări în practicile noastre sau în legislație. Modificările vor fi comunicate prin publicarea unei versiuni actualizate.
            </p>
            <p class="text-gray-700">
                Continuarea utilizării serviciilor {{ $locationName }} după modificarea politicii reprezintă acceptarea noii versiuni.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Contact</h2>
            <p class="text-gray-700 mb-4">
                Pentru întrebări, solicitări sau exercitarea drepturilor privind protecția datelor, vă rugăm să contactați {{ $locationName }}.
            </p>
            <p class="text-gray-700">
                Pentru plângeri privind protecția datelor, puteți contacta Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal (ANSPDCP) la adresa: B-dul G-ral Gheorghe Magheru 28-30, Sector 1, București sau pe site-ul www.dataprotection.ro.
            </p>
        </section>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 text-center">
                Prin acceptarea acestei politici de protecție a datelor, confirmați că ați citit, înțeles și acceptați modul în care {{ $locationName }} prelucrează datele cu caracter personal.
            </p>
        </div>
    </div>
</div>
@endsection

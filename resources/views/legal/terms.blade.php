@extends('layouts.app')

@section('title', 'Termeni și Condiții')

@section('page-title', 'Termeni și Condiții')

@section('content')
<div class="bg-white rounded-lg shadow p-6 md:p-8 max-w-4xl mx-auto">
    <div class="mb-6 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Termeni și Condiții</h1>
        <p class="text-gray-600">Versiunea {{ $version }} - Ultima actualizare: {{ date('d.m.Y') }}</p>
        <p class="text-sm text-gray-500 mt-2">{{ $locationName }}</p>
    </div>

    <div class="prose prose-lg max-w-none">
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introducere</h2>
            <p class="text-gray-700 mb-4">
                Bun venit la <strong>{{ $locationName }}</strong>. Prin utilizarea serviciilor noastre, acceptați în mod expres și necondiționat următorii termeni și condiții.
            </p>
            <p class="text-gray-700">
                Acești termeni reglementează accesul și utilizarea serviciilor oferite de {{ $locationName }} de către părinți/tutori legali pentru înregistrarea și gestionarea sesiunilor de joacă ale copiilor minori aflați în îngrijirea lor.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Definiții</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li><strong>{{ $locationName }}</strong> - locul de joacă care oferă servicii de joacă și divertisment pentru copii</li>
                <li><strong>Copil</strong> - minorul aflat în îngrijirea părinților/tutorilor legali</li>
                <li><strong>Părinte/Tutor</strong> - persoana cu responsabilitate legală pentru copil</li>
                <li><strong>Brățară RFID</strong> - dispozitiv electronic folosit pentru identificare și urmărire a timpului petrecut</li>
                <li><strong>Sesiune de joacă</strong> - perioada de timp în care copilul utilizează serviciile {{ $locationName }}</li>
                <li><strong>Regulament</strong> - regulile de bună conduită și siguranță aplicabile în {{ $locationName }}</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Regulamentul {{ $locationName }}</h2>
            <p class="text-gray-700 mb-4">
                Prin utilizarea serviciilor {{ $locationName }}, vă angajați să respectați următoarele reguli:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Copiii trebuie să fie supravegheați de un adult responsabil în timpul utilizării serviciilor</li>
                <li>Nu este permisă utilizarea agresivă sau periculoasă a echipamentelor de joacă</li>
                <li>Este interzisă introducerea de alimente sau băuturi în zonele de joacă (exceptând zonele desemnate)</li>
                <li>Copiii cu boli contagioase sau simptome de boală nu pot accesa serviciile</li>
                <li>Este obligatorie păstrarea liniștii și respectarea celorlalți vizitatori</li>
                <li>Toate echipamentele trebuie utilizate conform instrucțiunilor și destinației lor</li>
                <li>Nu este permisă distrugerea sau deteriorarea bunurilor {{ $locationName }}</li>
                <li>Este interzis fumatul în interiorul {{ $locationName }}</li>
                <li>Personalul {{ $locationName }} are dreptul să refuze accesul sau să solicite părăsirea spațiului în caz de nerespectare a regulamentului</li>
            </ul>
            <p class="text-gray-700">
                {{ $locationName }} își rezervă dreptul de a modifica regulamentul în orice moment. Modificările vor fi comunicate prin afișare la intrare sau prin alte mijloace de comunicare.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Condiții de Acces</h2>
            <p class="text-gray-700 mb-4">
                Serviciile {{ $locationName }} sunt destinate exclusiv copiilor minori și părinților/tutorilor lor legali.
            </p>
            <p class="text-gray-700 mb-4">
                Pentru a utiliza serviciile, trebuie să:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Aveți vârsta legală (minimum 18 ani) și capacitate legală deplină</li>
                <li>Furnizați informații corecte și complete despre copil și despre dumneavoastră</li>
                <li>Acceptați acești termeni și condiții în mod expres</li>
                <li>Acceptați politica de protecție a datelor cu caracter personal (GDPR)</li>
                <li>Respectați regulamentul {{ $locationName }}</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Înregistrarea Copilului</h2>
            <p class="text-gray-700 mb-4">
                La înregistrarea copilului, va trebui să furnizați următoarele informații:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Prenume și nume complet</li>
                <li>Data nașterii</li>
                <li>Informații despre alergii sau condiții medicale relevante (opțional, dar recomandat pentru siguranță)</li>
            </ul>
            <p class="text-gray-700 mb-4">
                De asemenea, veți furniza datele dumneavoastră de contact:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Nume complet</li>
                <li>Număr de telefon</li>
            </ul>
            <p class="text-gray-700">
                Vă angajați că toate informațiile furnizate sunt corecte, complete și actualizate. Orice modificare a informațiilor trebuie comunicată imediat {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Utilizarea Brățării RFID</h2>
            <p class="text-gray-700 mb-4">
                Brățara RFID este asignată copilului dumneavoastră pentru identificare și urmărire a timpului petrecut în {{ $locationName }}.
            </p>
            <p class="text-gray-700 mb-4">
                Sunteți responsabil pentru:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Întoarcerea brățării la sfârșitul fiecărei vizite</li>
                <li>Păstrarea brățării într-o stare bună</li>
                <li>Raportarea imediată a pierderii sau deteriorării brățării</li>
            </ul>
            <p class="text-gray-700">
                Pierderea sau deteriorarea brățării poate implica costuri de înlocuire în valoare de [suma] RON.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Sesiuni de Joacă și Facturare</h2>
            <p class="text-gray-700 mb-4">
                Sesiunea de joacă începe când brățara este scanată și se încheie când este scanată din nou pentru oprire.
            </p>
            <p class="text-gray-700 mb-4">
                Durata sesiunii este calculată automat, excluzând pauzele. {{ $locationName }} calculează costul pe baza tarifului stabilit și afișat public.
            </p>
            <p class="text-gray-700 mb-4">
                Sunteți responsabil pentru plata tuturor sesiunilor de joacă efectuate de copilul dumneavoastră. Facturarea se face la finalizarea fiecărei sesiuni sau la sfârșitul perioadei contractuale, conform politicii de facturare a {{ $locationName }}.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Responsabilități</h2>
            <p class="text-gray-700 mb-4">
                <strong>Responsabilitățile dumneavoastră:</strong>
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Asigurarea că copilul este supus supervizării corespunzătoare în timpul utilizării serviciilor</li>
                <li>Comunicarea oricăror alergii, condiții medicale sau restricții speciale ale copilului</li>
                <li>Respectarea regulamentului și politicilor {{ $locationName }}</li>
                <li>Plata la timp a serviciilor utilizate</li>
                <li>Înțelegerea și acceptarea riscurilor asociate activităților de joacă</li>
            </ul>
            <p class="text-gray-700 mb-4">
                <strong>Responsabilitățile {{ $locationName }}:</strong>
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Oferirea unui mediu sigur și adecvat pentru joacă, în conformitate cu standardele de siguranță</li>
                <li>Păstrarea confidențialității datelor personale conform GDPR</li>
                <li>Gestionarea corectă a sesiunilor și facturarea</li>
                <li>Asigurarea întreținerii regulate a echipamentelor și spațiilor</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Neasumarea Răspunderii</h2>
            <p class="text-gray-700 mb-4">
                <strong>IMPORTANT:</strong> Prin utilizarea serviciilor {{ $locationName }}, înțelegeți și acceptați că:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li><strong>{{ $locationName }} nu își asumă răspunderea</strong> pentru accidente, răniri sau leziuni suferite de copil sau de adult în timpul utilizării serviciilor, indiferent de cauză</li>
                <li><strong>{{ $locationName }} nu își asumă răspunderea</strong> pentru pierderi sau deteriorări ale bunurilor personale aduse în spațiile {{ $locationName }}</li>
                <li><strong>{{ $locationName }} nu își asumă răspunderea</strong> pentru accidente sau incidente cauzate de neglijență, utilizare necorespunzătoare a echipamentelor sau nerespectarea regulamentului</li>
                <li><strong>{{ $locationName }} nu își asumă răspunderea</strong> pentru accidente sau incidente cauzate de acțiunile altor vizitatori sau circumstanțe independente de controlul {{ $locationName }}</li>
            </ul>
            <p class="text-gray-700 mb-4">
                Utilizarea serviciilor {{ $locationName }} se face pe propria răspundere. Părinții/tutorii legali sunt singurii responsabili pentru siguranța și bunăstarea copiilor în timpul utilizării serviciilor.
            </p>
            <p class="text-gray-700">
                Prin acceptarea acestor termeni, renunțați expres la orice pretenții sau cereri de daune față de {{ $locationName }}, reprezentanții săi, personalul sau partenerii săi, în legătură cu utilizarea serviciilor.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Limitarea Răspunderii</h2>
            <p class="text-gray-700 mb-4">
                În măsura permisă de lege, {{ $locationName }} nu răspunde pentru:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Interruperi ale serviciilor din cauza problemelor tehnice sau întreținerii</li>
                <li>Erori în calcularea timpului sau a costurilor din cauza problemelor tehnice</li>
                <li>Modificări ale programului sau ale disponibilității serviciilor</li>
                <li>Daune indirecte sau consecințiale rezultate din utilizarea sau neutilizarea serviciilor</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Modificarea Termenilor</h2>
            <p class="text-gray-700 mb-4">
                {{ $locationName }} își rezervă dreptul de a modifica acești termeni și condiții în orice moment. Modificările vor fi comunicate prin publicarea unei versiuni actualizate sau prin afișare la intrare.
            </p>
            <p class="text-gray-700">
                Continuarea utilizării serviciilor după modificarea termenilor reprezintă acceptarea noilor termeni.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Rezilierea</h2>
            <p class="text-gray-700 mb-4">
                Vă puteți retrage din utilizarea serviciilor {{ $locationName }} în orice moment, cu condiția să achitați toate datoriile existente.
            </p>
            <p class="text-gray-700 mb-4">
                {{ $locationName }} își rezervă dreptul de a rezilia accesul la servicii în cazul:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                <li>Neplății facturilor</li>
                <li>Încălcării termenilor și condițiilor sau a regulamentului</li>
                <li>Furnizării de informații false sau incomplete</li>
                <li>Comportamentului inadecvat sau nerespectării regulamentului</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Legea Aplicabilă</h2>
            <p class="text-gray-700 mb-4">
                Acești termeni și condiții sunt guvernați de legile Republicii România. Orice dispute vor fi rezolvate de instanțele competente din România.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Contact</h2>
            <p class="text-gray-700 mb-4">
                Pentru întrebări sau clarificări privind acești termeni și condiții, vă rugăm să contactați {{ $locationName }}.
            </p>
        </section>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 text-center">
                Prin acceptarea acestor termeni și condiții, confirmați că ați citit, înțeles și acceptați în mod expres toate prevederile documentului, inclusiv neasumarea răspunderii pentru accidente și incidente.
            </p>
        </div>
    </div>
</div>
@endsection

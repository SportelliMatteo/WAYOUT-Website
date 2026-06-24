@extends('layouts.app', [
    'title' => 'Chi siamo - Wayout',
    'description' => 'Scopri la missione di Wayout e come uniamo persone ai tavoli.'
])

@section('content')
<section class="bg-white mt-2">
    <div class="py-8 px-4 mx-auto max-w-screen-2xl text-center lg:py-16 lg:px-6">
        <div class="mx-auto mb-8 max-w-3xl lg:mb-16 flex flex-col items-center">
            <h2 class="mb-4 text-4xl font-extrabold tracking-tight lg:text-5xl">
                THIS IS WAYOUT
            </h2>
            <p class="font-light text-gray-500 sm:text-xl max-w-2xl">
                WAYOUT nasce per realizzare una visione: unire persone che non si sono mai viste prima e portarle a fare serata insieme.
                Abbiamo inventato un’esperienza e l’abbiamo chiamata social clubbing.
            </p>

            <img
                class="w-full max-w-xxl h-auto rounded-lg mt-5 shadow-xl"
                src="/images/about/about.jpg"
                alt="Wayout about"
            />
        </div>

        <div class="mx-auto max-w-6xl text-left">
            <h2 class="mt-10 text-3xl font-semibold tracking-tight">
                Il progetto in sintesi
            </h2>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                WAYOUT è una nuova iniziativa nel settore della vita notturna che punta a portare una ventata di aria fresca nel contesto del clubbing italiano ed europeo.
                Siamo un giovanissimo team di artisti, marketer, sviluppatori, content creator, ma soprattutto fan della notte e del divertimento, che hanno deciso di mettersi insieme per realizzare una visione: unire persone che non si sono mai viste prima e portarle a fare serata insieme.
                Il nostro obiettivo è permettere ai giovani di tutta Italia di conoscere nuovi amici con cui condividere le stesse vibes notturne!
            </p>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                Più precisamente tramite l'app di WAYOUT chiunque può partecipare a tavoli in discoteca organizzati da altri utenti oppure creare il proprio tavolo a cui gli altri possono unirsi.
            </p>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                Se stai cercando nuovi amici con cui uscire e divertirti, WAYOUT fa proprio al caso tuo. Non sai con chi fare serata nel weekend? Unisciti ad un tavolo WAYOUT e conosci gente nuova!
            </p>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                Quella che puoi vivere tramite WAYOUT è un’esperienza totalmente nuova, sorprendente ed emozionante. Abbiamo perfino coniato un termine per definirla: noi la chiamiamo SOCIAL CLUBBING.
            </p>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                Noi founder siamo tutti ex studenti ed ognuno di noi nel corso degli anni universitari ha vissuto la stessa sensazione: una grande voglia di vivere al meglio gli ultimi anni di spensieratezza, di socializzare, di fare esperienze nuove e creare ricordi indelebili con persone vere e che avevano la stessa nostra voglia di divertirsi. Ma spesso il problema era proprio trovare le persone giuste con cui condividere questa voglia di allegria, leggerezza e divertimento.
            </p>
            <p class="leading-7 mt-6 text-lg text-slate-700">
                Se anche tu hai vissuto la stessa sensazione, WAYOUT è la soluzione che cercavi: partecipa ad un tavolo già formato o creane uno tu!
            </p>
        </div>
    </div>
</section>
@endsection

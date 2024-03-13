CRM se snažím co mě volná chvilka dovolí stavět co nejvíce odděleně, ale i tak je to málo :)

Popis architektura MVC
CRM/
├── app/              ← adresář s aplikací
│   ├── Cert/         ← dkim podpis emailů
│   ├── Presenters/   ← třídy presenterů
│   │   └── templates/← šablony
│   ├── Model/        ← Modely pro databaze
│   ├── Router/       ← konfigurace URL adres
│   └── Bootstrap.php ← zaváděcí třída Bootstrap
├── doklady/              ← soubory s doklady pro klienty
├── klienti/              ← soubory s avatary klientů
├── config/           ← konfigurační soubory
├── log/              ← logování chyb
├── profil/           ← soubory s avatary zaměstanců (users)
├── temp/             ← dočasné soubory, cache, …
├── vendor/           ← knihovny instalované Composerem
│   └── autoload.php  ← autoloading všech nainstalovaných balíčků
└── www/              ← veřejný adresář - jediný přístupný z prohlížeče
    └── index.php     ← prvotní soubor, kterým se aplikace spouští
Aplikace CRM je rozdělana do presentů podle divizí ( TravelPresenter.php apod. ), stejně tak jsou rozděleny modely ( TravelModel.php ) a samozřejmě templates jsou ve stejné logice.
Přikládám rozbor logiky pro divizi Travel. Strom ukazuje obsah složky app.

app/
├── Presenters/       ← adresář s presenty nejen pro travel je zde soubor TravelPresenter.php
│   ├── templates/    ← adresář s templates, v rootu obsahuje @layout.latte, a šablony pro menu, která jsou rozdělana podle divizí pro travel travelMenu.latte
│   │   └── travel/   ← adresář s šablony v latte pro divizi travel například akce.latte nebo detailAkce.latte
│   ├── Model/        ← Zde jsou modely (slouží pro odělení logiky pro příkazy do db) pro travel je zde TravelModel.php
│   ├── Services/     ← Zde jsou převážně oddělené formuláře, pro travel FormCrmFactory.php, ale i šablony pro shortcode
│   ├── Router/       ← konfigurace URL adres
│   └── Bootstrap.php ← zaváděcí třída Bootstrap

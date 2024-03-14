CRM pro BPR se snažím co mě čas dovolí (primární práce není programování), stavět co nejvíce odděleně, ale i tak je to málo :) motivovat kolegy (zatím pracuji na app sám) a ano je v kódu použit český jazyk (jestli bude kolega, stojí za přepsání). V průběhu času nahraji komplet app i když bude pořád v testovací a vývojové fázy (app potřebuje alespoň minimální péči).

Aplikace je napsána v [Nette](https://nette.org/cs/) a [Latte](https://latte.nette.org/)

Soubory kódů, co jsem nahrál obsahují ukázku routru, modelu, formulářů, presenteru, tak i šablonovacího souboru latte a composer.json, pro člověka, který nezná Nette, tak soubor index.php a Bootstrap.php, který spouští aplikaci, ale rozhodně pro pochopení je znalost Nette nebo Laravel celkem podstatná.

Aplikace CRM je rozdělana do presentů podle divizí ( TravelPresenter.php apod. ), stejně tak jsou rozděleny modely ( TravelModel.php ) a samozřejmě templates jsou ve stejné logice.

Popis architektura MVC

![image](https://github.com/MiRdACz/CRM/assets/9698726/42a25108-b7d0-45aa-bf34-9e2afb3178cf)
![image](https://github.com/MiRdACz/CRM/assets/9698726/1d478cda-4fad-442f-a126-24aa7e525bff)

Databáze, všeříkající ukázka tvorby tabulek pro zeme a mesto (lepší by bylo mesta - držet plurál)

CREATE TABLE IF NOT EXISTS `zeme` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazev` varchar(255) COLLATE utf8mb3_czech_ci NOT NULL,
  `latitude` varchar(255) COLLATE utf8mb3_czech_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb3_czech_ci DEFAULT NULL,
  `zoom` varchar(255) COLLATE utf8mb3_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;

CREATE TABLE IF NOT EXISTS `mesto` (
 `id` int UNSIGNED NOT NULL AUTO_INCREMENT,  
 `nazev` varchar(255) COLLATE utf8mb3_czech_ci NOT NULL,  
 `zeme_id` int UNSIGNED NOT NULL,  
 PRIMARY KEY (`id`),  
 KEY `zeme_id` (`zeme_id`)  
) ENGINE=InnoDB AUTO_INCREMENT=558 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;


Použité třetí strany v aplikaci

na Javascript není využíváno npm, nepotřebuji pro běh aplikace.
Javascript je zaveden ručně.

Nejdůležitější JS rozšíření pro aplikaci je [Naja](https://naja.js.org/#/) , která zajišťuje psaní AJAXových scriptů a komunikaci v Nette.

Editor pro editaci zajišťuje JS [tinymce](https://www.tiny.cloud/)

Pro frontend je zde rozšíření lozad, do budoucna se s největší pravděpodobností odstraní.
[Bootstrap](https://getbootstrap.com/)https://getbootstrap.com/ pro css



CRM pro BPR se snažím co mě čas dovolí, stavět co nejvíce odděleně, ale i tak je to málo :) motivovat kolegy a ano je v kodu použit český jazyk

Aplikace je napsána v [Nette](https://nette.org/cs/) a [Latte](https://latte.nette.org/)

Soubory kodů co jsem nahrál obsahují ukázku modelu, presenteru, tak i šablonovacího souboru latte a composer.json.

Aplikace CRM je rozdělana do presentů podle divizí ( TravelPresenter.php apod. ), stejně tak jsou rozděleny modely ( TravelModel.php ) a samozřejmě templates jsou ve stejné logice.

Popis architektura MVC

![image](https://github.com/MiRdACz/CRM/assets/9698726/42a25108-b7d0-45aa-bf34-9e2afb3178cf)
![image](https://github.com/MiRdACz/CRM/assets/9698726/1d478cda-4fad-442f-a126-24aa7e525bff)

Databáze, vše říkající ukázka tvorby db

CREATE TABLE IF NOT EXISTS `mesto` (
 `id` int UNSIGNED NOT NULL AUTO_INCREMENT,  
 `nazev` varchar(255) COLLATE utf8mb3_czech_ci NOT NULL,  
 `zeme_id` int UNSIGNED NOT NULL,  
 PRIMARY KEY (`id`),  
 KEY `zeme_id` (`zeme_id`)  
) ENGINE=InnoDB AUTO_INCREMENT=558 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;


Použité třetí strany v aplikaci

Javascriptové rozšíření. Na Javascript není využíváno npm, není třeba využívat pro běh aplikace.
Javascript je zaveden ručne.

Nejdůležitější JS rozšíření pro aplikaci je Naja , která zajišťuje psaní AJAXových scriptů a komunikaci v Nette.

Editor pro editaci zajišťuje JS tinymce

Pro frontend je zde rozšíření lozad, do budoucna se s největší pravděpodobností odstraní.
Bootstrap pro css



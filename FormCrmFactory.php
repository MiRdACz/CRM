<?php
namespace App\Services;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Security\User;
use Nette\Http\Response;
use Nette\Utils\Random;
use App\Model\EmailModel;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nette\Http\Request;
use App\Model\TravelModel;
use h4kuna\Ares\Ares;

class FormCrmFactory
{
    private $travelModel;

    public function __construct(TravelModel $travelModel)
    {
        $this->travelModel = $travelModel;
    }

    public function mestoSluzby(): Form
    {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $zeme = intval($httpRequest->getQuery('zeme'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $category =[];
                $category += [0 => 'Země není vybrána'];
                foreach ($this->travelModel->selectKategorie() as $index => $cat) {
                    $category += [$index => $cat];
                }
        $form->addSelect('categorie', 'Kategorie:',$category )
            ->setHtmlAttribute('class', 'form-select');
        $form->addSelect('mesto_id', 'Město:', $this->travelModel->selectMesto($zeme))
            ->setHtmlAttribute('class', 'form-select')->setPrompt('Vyberte kategorii');
        $form->addSubmit('send', 'Hledat');

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }
    public function kategorySluzby(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $category =[];
        $category += [0 => 'Země není vybrána'];
        foreach ($this->travelModel->SluzbyKategorie() as $index => $cat) {
            $category += [$index => $cat];
        }
        $form->addSelect('categorie', 'Kategorie:',$category )
            ->setHtmlAttribute('class', 'form-select');

        $form->addSelect('country', 'Stát:', $this->travelModel->selectZeme())
		  ->setHtmlAttribute('class', 'form-select')
          ->setPrompt('Vyberte stát');


        $form->addSubmit('send', 'Hledat');

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }
	public function akce(): Form
    {				
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $category = [];
        $firma = [];
        $datum = [];
        $categoryData = $this->travelModel->akceKategorie();
        $firmaData = $this->travelModel->firmy();
		$datumRok = $this->travelModel->roky();
		foreach ($datumRok as $dat) {
            $datum += [$dat => $dat];
        }
        foreach ($categoryData as $cat) {
            $category += [$cat['id'] => $cat['nazev']];
        }
        foreach ($firmaData as $fir) {
            $firma += [$fir['id'] => $fir['nazev']];
        }		
		$form->addSelect('datum', 'Rok:', $datum)
		  ->setHtmlAttribute('class', 'form-select form-select-sm form-select my-2')
          ->setPrompt('Vyberte rok');
		$form->addSelect('country', 'Stát:', $this->travelModel->selectZeme())
		  ->setHtmlAttribute('class', 'form-select form-select-sm')
          ->setPrompt('Vyberte stát');
        $form->addSelect('categorie', 'Kategorie:', $category)
            ->setHtmlAttribute('class', 'form-select form-select-sm')->setPrompt('všechny');
        $form->addSelect('firma', 'Firma:', $firma)
            ->setHtmlAttribute('class', 'form-select form-select-sm')->setPrompt('všechny');
        $form->addSubmit('send', 'hledat');
        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }
	public function akceMesto(): Form
	{
		$factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $zeme = intval($httpRequest->getQuery('zeme'));
		$form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$form->addSelect('mesto_id', 'Město:', $this->travelModel->selectMesto($zeme))
            ->setHtmlAttribute('class', 'form-select form-select-sm')->setPrompt('Vyberte si');
		$form->addSubmit('send', 'hledat');
        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;	
	}
    public function kategory(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $category = [];
        $firma = [];
        $categoryData = $this->travelModel->kategorie();
        $firmaData = $this->travelModel->firmy();
        foreach ($categoryData as $cat) {
            $category += [$cat['id'] => $cat['nazev']];
        }
        foreach ($firmaData as $fir) {
            $firma += [$fir['id'] => $fir['nazev']];
        }
        $form->addSelect('categorie', 'Kategorie:', $category)
            ->setHtmlAttribute('class', 'form-select')->setPrompt('všechny');
        $form->addSelect('firma', 'Firma:', $firma)
            ->setHtmlAttribute('class', 'form-select')->setPrompt('všechny');
        $form->addSubmit('send', 'Registrovat');
        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }

    public function kategoryKlient(/* parametry */): Form
	{
		$form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Zadejte prosím mázev');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-3');
		$form->onSuccess[] = [$this, 'processForm'];

		return $form;
	}

    public function processForm(Form $form, array $values): void
	{
        try {
            // zpracování formuláře
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));

            if ($userId) {
                $this->travelModel->klientKategoryEdit($values,$userId);
            } else {
                $this->travelModel->klientKategory($values);
            }

        } catch (AnyModelException $e) {
            $form->addError('...');
        }
    }

    // pridani klienta

    public function AddKlient(): Form
	{
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $firmy = [];
        $dataParent = $this->travelModel->firmy();
        foreach ($dataParent as $firma) {
            $firmy += [$firma['id'] => $firma['nazev']];
        }

        $category = [];
        $categoryData = $this->travelModel->kategorie();
        foreach ($categoryData as $cat) {
            $category += [$cat['id'] => $cat['nazev']];
        }
        $form->addCheckboxList('category_id', 'Kategorie:', $category)
            ->setHtmlAttribute('class', 'form-check-input');

        $form->addSelect('firma_id', 'Firma:', $firmy)->setHtmlAttribute('class', 'form-select')->setPrompt('Vyberte firmu')->setRequired('Vyberte firmu');
        $form->addUpload('img', 'Avatar:')
            ->addRule($form::IMAGE, 'Avatar musí být JPEG, PNG, GIF or WebP.');

        $form->addUpload('doklad_pas', 'Pas:')
        ->addRule(Form::MIME_TYPE,'%label musí být ve formátu PDF.',['application/pdf','image/*']);
        $form->addUpload('doklad_rp', 'Řidičský průkaz:')
            ->addRule(Form::MIME_TYPE,'%label musí být ve formátu PDF.',['application/pdf']);
        $form->addUpload('doklad_pojistka', 'Pojistka:')
            ->addRule(Form::MIME_TYPE,'%label musí být ve formátu PDF.',['application/pdf']);
        //    ->addRule($form::IMAGE, 'Pas musí být JPEG, PNG, GIF or WebP.');

        $form->addText('jmeno', 'Jméno:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addText('prijmeni', 'Příjmení:')->setHtmlAttribute('class', 'form-control');
        $form->addText('date', 'Datum narození:')->setHtmlAttribute('class', 'form-control')
            ->setHtmlType('date');
        $form->addText('telefon', 'Telefon:')->setHtmlAttribute('class', 'form-control')
            ->setHtmlType('tel')
            ->setEmptyValue('+420');
        $form->addText('telefon2', 'Telefon soukromý:')->setHtmlAttribute('class', 'form-control')
            ->setHtmlType('tel')
            ->setEmptyValue('+420');
        $form->addEmail('email','Email')->setHtmlAttribute('class', 'form-control');
        $form->addEmail('email2','Email soukromý')->setHtmlAttribute('class', 'form-control');
        $form->addInteger('provize', 'Provize:')->setHtmlAttribute('class', 'form-control');
        $form->addText('pojisteni', 'Vypršení pojištěni:')->setHtmlAttribute('class', 'form-control')->setHtmlType('date');
        $form->addTextArea('poznamka', 'Poznámka:')->setHtmlAttribute('class', 'form-control tinyMCE')->setHtmlAttribute('cols', '50');
        $form->addTextArea('zdravotni', 'Zdravotní poznámka:')->setHtmlAttribute('class', 'form-control tinyMCE')->setHtmlAttribute('cols', '50');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->addSubmit('smazat', 'Smazat obrázek');
        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }

// Sluzba pridat
    public function pridatSluzbu(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');


        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Zadejte prosím název');
        $form->addText('telefon', 'Telefon:')->setHtmlAttribute('class', 'form-control')->setRequired('Zadejte prosím telefon');
        $form->addEmail('email', 'Email:')->setHtmlAttribute('class', 'form-control');
        $form->addText('web', 'Web:')->setHtmlAttribute('class', 'form-control');
        $form->addText('latitude', 'Latitude:')->setHtmlAttribute('class', 'form-control');
        $form->addText('longitude', 'Longitude:')->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('poznamka', 'Poznámka:')->setHtmlAttribute('class', 'form-control tinyMCE');

        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));
            if ($userId) {
                $this->travelModel->upCrmSluzba($values,$userId);
                return $userId;
            } else {
                $this->travelModel->newCrmSluzba($values);
                return true;
            }
        };
        return $form;

    }
    public function pridatSluzbuKategorie(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));
            if ($userId) {
                $this->travelModel->upCrmSluzbaKategorie($values,$userId);
                return $userId;
            } else {
                $this->travelModel->newCrmSluzbaKategorie($values);
                return true;
            }
        };
        return $form;

    }
    // Firma pridat
    public function pridatFirmu(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addText('ico', 'Ičo:')->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('userId'));
            if ($userId) {
                if($values->ico != null){
                    $ares = new Ares();
                    if(!$ares->loadData($values->ico)){
                        $values->ico = null;
                    }
                }
                $this->travelModel->upCrmFirma($values,$userId);
                return $userId;
            } else {
                if($values->ico != null){
                    $ares = new Ares();
                    if(!$ares->loadData($values->ico)){
                        $values->ico = null;
                    }
                }
                $this->travelModel->newCrmFirma($values);
                return true;
            }
        };
        return $form;
    }
    // zeme
    public function pridatZeme(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addText('latitude', 'Latitude:')->setHtmlAttribute('class', 'form-control');
        $form->addText('longitude', 'Longitude:')->setHtmlAttribute('class', 'form-control');
        $form->addText('zoom', 'Zoom:')->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $zeme = intval($httpRequest->getQuery('id'));
            if ($zeme) {
                $this->travelModel->upCrmZeme($values,$zeme);
                return $zeme;
            } else {
                $this->travelModel->newCrmZeme($values);
                return true;
            }
        };
        return $form;

    }
    // mesto
    public function pridatMesto(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $mesto = intval($httpRequest->getQuery('id'));
            $zeme = intval($httpRequest->getQuery('zem'));
            if ($mesto) {
                $this->travelModel->upCrmMesto($values,$mesto);
            }
            if ($zeme) {
                $this->travelModel->newCrmMesto($values,$zeme);
            }
        };
        return $form;

    }
	// inspirace formular
	public function pridatInspirace(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');
        $form->addTextArea('poznamka', 'Poznamka:')->setHtmlAttribute('class', 'form-control tinyMCE');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            if ($id) {
                $this->travelModel->editInspirace($id,$values);
                return $id;
            } else {
                $this->travelModel->newInspirace($values);
                return true;
            }
        };
        return $form;
    }
	
	// destinace formular
	public function pridatDestinace(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');
        $form->addTextArea('poznamka', 'Poznamka:')->setHtmlAttribute('class', 'form-control tinyMCE');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            if ($id) {
                $this->travelModel->editDestinace($id,$values);
                return $id;
            } else {
                $this->travelModel->newDestinace($values);
                return true;
            }
        };
        return $form;
    }
	// Destinace mesto
	public function pridatDestinaceMesto(): Form
    {
		$factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();        
        $idStat = intval($httpRequest->getQuery('idStat'));
			
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');
        $form->addHidden('destinace_id')->setDefaultValue($idStat);
        $form->addTextArea('poznamka', 'Poznamka:')->setHtmlAttribute('class', 'form-control tinyMCE');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            $idStat = intval($httpRequest->getQuery('idStat'));
            if ($id) {
                $this->travelModel->editDestinaceMesto($id,$idStat,$values);
                return $id;
            } else {
                $this->travelModel->newDestinaceMesto($idStat,$values);
                return true;
            }
        };
        return $form;
    }
	// misto formaulare
	public function pridatMistoKategorie(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');        
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            if ($id) {
                $this->travelModel->editMistoKategorie($id,$values);
                return $id;
            } else {
                $this->travelModel->newMistoKategorie($values);
                return true;
            }
        };
        return $form;
    }
	public function pridatMista(): Form
	{
		$form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addSelect('category_id', 'Kategorie:', $this->travelModel->selectMistaKategorie())
		  ->setHtmlAttribute('class', 'form-select form-select-sm')
          ->setPrompt('Vyberte kategorii')->setRequired('Vyplňte kategorii');
		$form->addSelect('zeme_id', 'Stát:', $this->travelModel->selectZeme())
		  ->setHtmlAttribute('class', 'form-select form-select-sm')
          ->setPrompt('Vyberte stát')->setRequired('Vyplňte stát');
		$form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');
		$form->addText('latitude', 'Latitude:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte body pro mapu');      
		$form->addText('longitude', 'Longitude:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte body pro mapu');		
        $form->addTextArea('poznamka', 'Poznamka:')->setHtmlAttribute('class', 'form-control tinyMCE');
				
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            if ($id) {
                $this->travelModel->editMisto($id,$values);
                return $id;
            } else {
                $this->travelModel->newMisto($values);
                return true;
            }
        };
        return $form;
	}
	public function kategoryMista(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $category =[];
        $category += [0 => 'Země není vybrána'];
        foreach ($this->travelModel->mistaKategorie() as $index => $cat) {
            $category += [$index => $cat];
        }
        $form->addSelect('categorie', 'Kategorie:',$category )
            ->setHtmlAttribute('class', 'form-select');

        $form->addSelect('country', 'Stát:', $this->travelModel->selectZeme())
		  ->setHtmlAttribute('class', 'form-select')
          ->setPrompt('Vyberte stát');


        $form->addSubmit('send', 'Hledat');

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }
    // hotely loginy
    public function pridatHotelyKategorie(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addInteger('nazev', 'Zadejte počet hvězd:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));
            if ($userId) {
                $this->travelModel->upCrmHotelKategorie($values,$userId);
                return $userId;
            } else {
                $this->travelModel->newCrmHotelKategorie($values);
                return true;
            }
        };
        return $form;

    }
	public function pridatHotelyKategorieHouse(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Typ:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));
            if ($userId) {
                $this->travelModel->upCrmHotelKategorieHouse($values,$userId);
                return $userId;
            } else {
                $this->travelModel->newCrmHotelKategorieHouse($values);
                return true;
            }
        };
        return $form;

    }
    public function pridatHotelyLogin(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte název');
        $form->addTextArea('poznamka', 'Poznamka:')->setHtmlAttribute('class', 'form-control tinyMCE');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $id = intval($httpRequest->getQuery('id'));
            if ($id) {
                $this->travelModel->editHotelyTravelLogin($id,$values);
                return $id;
            } else {
                $this->travelModel->newHotelyTravelLogin($values);
                return true;
            }
        };
        return $form;
    }
    public function mestoHotely(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $zeme = intval($httpRequest->getQuery('zeme'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $category =[];
        $category += [0 => 'Země není vybrána'];
        foreach ($this->travelModel->HotelyKategorie() as $index => $cat) {
            $category += [$index => $cat];
        }
        $form->addSelect('categorie', 'Kategorie:',$category )
            ->setHtmlAttribute('class', 'form-select');
        $form->addSelect('mesto_id', 'Město:', $this->travelModel->selectMesto($zeme))
            ->setHtmlAttribute('class', 'form-select')->setPrompt('Vyberte kategorii');
		$form->addHidden('house');	
        $form->addSubmit('send', 'Hledat');

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }
    public function kategoryHotely(): Form
    {
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');

        $category =[];
        $category += [0 => 'Země není vybrána'];
        foreach ($this->travelModel->HotelyKategorie() as $index => $cat) {
            $category += [$index => $cat];
        }
        $form->addSelect('categorie', 'Kategorie:',$category )
            ->setHtmlAttribute('class', 'form-select');
		
		$form->addHidden('house');	

        $form->addSelect('country', 'Stát:', $this->travelModel->selectZeme())
            ->setHtmlAttribute('class', 'form-select')
            ->setPrompt('Vyberte stát');


        $form->addSubmit('send', 'Hledat');

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {

        };
        return $form;
    }

    //akce
    public function pridatAkceKategorie(): Form
    {
        $factory = new Nette\Http\RequestFactory;
        $httpRequest = $factory->fromGlobals();
        $id = intval($httpRequest->getQuery('id'));
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addText('nazev', 'Zadejte název:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyplňte jméno');
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success mt-2');
        $form->onSuccess[] = function (UI\Form $form, $values) {
            $factory = new Nette\Http\RequestFactory;
            $httpRequest = $factory->fromGlobals();
            $userId = intval($httpRequest->getQuery('id'));
            if ($userId) {
                $this->travelModel->upCrmAkceKategorie($values,$userId);
                return $userId;
            } else {
                $this->travelModel->newCrmAkceKategorie($values);
                return true;
            }
        };
        return $form;

    }
}

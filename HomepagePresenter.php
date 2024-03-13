<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use Nette\Application\UI;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Http\Response;
use Nette\Utils\DateTime;
use Nette\Utils\Image;
use App\Services\FactoryFilterMonth;
use App\Services\FactoryFilterSnippetContent;
use App\Services\FactoryShortCode;
use App\Services\FormLogin;
use App\Model\PageModel;
use App\Model\ArticleModel;
use App\Model\ShortCodeModel;
use Nette\Utils\FileSystem;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    protected $factoryFilterMonth;
    protected $factoryFilterSnippetContent;
    /** @var ShortCodeModel @inject */
    public $shortCodeModel;
    /** @var FactoryShortCode @inject */
    public $factoryShortCode;
    /** @var Nette\Http\Response */
    private $http;
    /** @var FormLogin @inject */
    public $formFactory;

    private $articleModel;
    private $pageModel;
    private $emailModel;

    public function __construct(PageModel $pageModel,ShortCodeModel $shortCodeModel,ArticleModel $articleModel,Nette\Http\Response $http)
    {
        $this->pageModel = $pageModel;
        $this->shortCodeModel = $shortCodeModel;
        $this->articleModel = $articleModel;
        $this->http = $http;
    }

    protected function beforeRender()
    {
        /** Filter pro ceske mesice a utrzek z textu */
       $this->template->addFilter('czechMonth', new FactoryFilterMonth());
       $this->template->addFilter('snippetContent', new FactoryFilterSnippetContent());
       /** menu */
       $this->template->url = $this->getParameter('url');	   
	   
    }
	
    protected function shortCode($content,$shortcode)
    {
        if($content === null){return false;}
        if (Strings::contains($content, '[[kontakt]]') === true)
        {
            foreach($shortcode as $index=>$item) {
                 $parametrsContact = array('title_email' => $item->contact_title_email, 'content' => $item->contact_content, 'title' => $item->contact_title, 'sendbtn' => $item->contact_send, 'webtime' => time());
                 $content = Strings::replace($content, "~\[\[kontakt\]\]~", $this->template->renderToString(dirname(__DIR__) . '/Services/contact.latte', $parametrsContact));
            }
        }
        if (Strings::contains($content, '[[formular]]') === true)
        {
            $formInput = $this->shortCodeModel->getShortFormInput();
            foreach($shortcode as $index=>$item) {
                $parametrsForm = array('inputs' => $formInput,'form_title' => $item->form_title);
                $content = Strings::replace($content, "~\[\[formular\]\]~", $this->template->renderToString(dirname(__DIR__) . '/Services/form.latte', $parametrsForm));
            }
        }
        return $content;
    }

    public function renderDefault(string $url='')
    {
		/* overeni prav */
	   if (!$this->getUser()->isAllowed('user')) {
            //throw new Nette\Application\ForbiddenRequestException;
            $this->redirect('Homepage:sign');exit;
       }
        
        /** Stranka pro prvni zobrazeni, zakladne url je prazdne prvni stranka */
        $pageOrder = $this->pageModel->pageOrderSort();
        /** menu */
        $this->template->menus = $pageOrder;
        /** zabezpeceni IP block */
        $httpRequest = $this->getHttpRequest();
        $ip = $httpRequest->getRemoteAddress();
        $this->articleModel->blockIpUrl($url,$ip);
        /** vytahneme si obsah podle url */
        $page = $this->pageModel->getPageFromUrl($url, $pageOrder);
        if($page === false){$this->error('Stránka nebyla nalezena');}
        /** shortCode */
        $shortCodeDb = $this->shortCodeModel->getShortCode();
        $shortCode = $this->factoryShortCode->shortCode($page[0]['content'],$url,$shortCodeDb);
        $shortCode = $this::shortCode($shortCode,$this->shortCodeModel->getShortCodeOther());
        /** Logo a footer */
        $logoFooter = $this->pageModel->getLogoFooter();
        $this->template->footer_content = $this->factoryShortCode->shortCode($logoFooter->footer_content,null,$shortCodeDb);
        $this->template->logo = $logoFooter;
        /** pages */
        $this->template->page = $shortCode;
        $this->template->pageTitle = $page[0]['title'];
        $this->template->pageDescription = $page[0]['description'];
        $this->template->pageKw = $page[0]['kw'];
        /** Blog */
        if($url === 'blog'){
            $page = intval($this->getParameter('stranka'));
            if(!$page){ $page =1; }
            // Vytáhneme si publikované články
            $posts = $this->articleModel->findPublishedArticles();
            // vytahne vsechny clanky pro doporucene
            $this->template->postsRecomended = $this->articleModel->RecomendedArticles();
            // a do šablony pošleme pouze jejich část omezenou podle výpočtu metody page
            $lastPage = 0;
            $this->template->posts = $posts->page($page, 5, $lastPage);
            // a také potřebná data pro zobrazení možností stránkování
            $this->template->pageFirst = $page;
            $this->template->lastPage = $lastPage;
        }




    }
    public function menuFooterLogo()
    {
        /** Stranka pro prvni zobrazeni, zakladne url je prazdne prvni stranka */
        $pageOrder = $this->pageModel->pageOrderSort();
        /** menu */
        $this->template->menus = $pageOrder;
        /** Logo a footer */
        $shortCodeDb = $this->shortCodeModel->getShortCode();
        $logoFooter = $this->pageModel->getLogoFooter();
        $this->template->footer_content = $this->factoryShortCode->shortCode($logoFooter->footer_content,null,$shortCodeDb);
        $this->template->logo = $logoFooter;
    }
	/** detail uživatele BPR */
	public function renderBprUser(int $id)
	{
		$this::menuFooterLogo();
		
		if(!$this->pageModel->getBprUser($id)){
			$this->flashMessage("Chyba uživatel BPR neexistuje", 'alert-danger');
			$this->redirect('Homepage:default');exit;
		}else{
			$user_crm = $this->pageModel->getBprUser($id)->fetch();
			$this->template->bpruser = $user_crm;
			$img = $user_crm->avatar;
			if($img != null){
			$this->template->img = Image::fromFile(dirname(dirname(__DIR__)).'/profil/'.$id.'/avatar/'.$img);
			}else{
				$this->template->img =null;
			}
            
		}
	}
    

public function renderGlampingMapa()
    {
	$this::menuFooterLogo();
        $this->template->sluzby = $this->pageModel->addGlampAll();
    }
    protected function createComponentAddGlamp(): Form
	{
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addUpload('user', 'Soubor xlsx:')->setHtmlAttribute('class', 'form-control')->setRequired('Vyberte soubor');;
        $form->addSubmit('send', 'Uložit')->setHtmlAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'formAddGlampSucceeded'];
        return $form;
    }
    public function formAddGlampSucceeded(Form $form, $data): void
	{

        try {
            if ($form['send']->isSubmittedBy()) {

                $filePath = dirname(dirname(__DIR__)).'/www/excel/';
                FileSystem::normalizePath($filePath);
                $existingDir = FileSystem::createDir($filePath);
                $fileName = 'ca05cc42G30mMgf.xlsx';
                $data['user']->move($filePath . $fileName);

                if ( $xlsx = SimpleXLSX::parse('excel/ca05cc42G30mMgf.xlsx') ) {

                    $xlsxAll = $xlsx->rowsEx();
                    $error =[];
                    foreach($xlsxAll as $i => $x ){

                            $value['nazev'] = $x[0]['value'];
                            $value['druh'] = $x[1]['value'];
                            $value['web'] = $x[2]['value'];
                            $value['gps'] = $x[3]['value'];
                            $value['misto'] = $x[4]['value'];
                            $value['email'] = $x[5]['value'];
                            $value['telefon'] = $x[6]['value'];

                            $this->pageModel->addGlamp($value);
                    }

                } else {
                    $this->flashMessage(SimpleXLSX::parseError(), 'alert-warning');
                    $this->redirect('Travel:pridatHromadneKlienta');
                }
                FileSystem::delete('../www/excel/' . $fileName);
                //$this->pageModel->newCrmUser($data);
                if(count($error) >0){
                    $flash ='';
                    foreach ($error as $e) {
                        $flash .= $e.' ';
                    }

                    $this->flashMessage('Klienti byli přidáni! Upozornění emaily již existují. Duplicita u '.$flash, 'alert-alert');
                }else{
                    $this->flashMessage('Klienti uloženi ', 'alert-success');
                }
                $this->redirect('Homepage:glampingMapa');
            }
        }catch (PDOException $e) {
            $this->database->rollBack();
            throw $e; // pošlu to dál
        }
    }
    /** aktivace */
    public function renderActive(){

        /** aktive get parametrs */
        $email = $this->getParameter('email');
        $active_key = $this->getParameter('key');
        if(!$email || !$active_key){
            $this->redirect('Homepage:default');exit;
        }
        $checkActive = $this->pageModel->check($email,$active_key);
        if($checkActive){
            $this->pageModel->checkTrue($email,$active_key,$checkActive['id']);
            $this->flashMessage("Váš účet je aktivován!", 'alert-success');
            $this->redirect('sign');exit;
        }
        else{
            $this->flashMessage("Chyba! Kontaktujte administrátora", 'warning-success');
            $this->redirect('Homepage:default');exit;
        }
    }
    /** registrace */
    public function renderRegistration()
    {
        $this::menuFooterLogo();
        $httpRequest = $this->getHttpRequest();
        $ip = $httpRequest->getRemoteAddress();
        $this->template->block = $this->articleModel->block($ip);
        $this->template->ip = $ip;
        $check = $this->getParameter('heslo');
        if($check == 'af1S4Er455Rhj5$ss562a'){
            $this->template->admin = "ok";
        }else{
            $this->template->admin = "not";
        }
    }
    /** Prihlaseni */
    public function renderSign(){
        $this::menuFooterLogo();
        $httpRequest = $this->getHttpRequest();
        $ip = $httpRequest->getRemoteAddress();
        $this->template->block = $this->articleModel->block($ip);
        $this->template->ip = $ip;
        $this->template->webtime = time();
    }
    /** Akce Odhlaseni */
    public function actionOut(): void
    {
        if (!$this->getUser()->isLoggedIn()) { $this->redirect('Homepage:sign');exit; }
        $this->getUser()->logout(true);
        $this->flashMessage('Odhlášení bylo úspěšné.','alert-success');
        $this->redirect('Homepage:');exit;
    }
    /** Obnova hesla */
    public function renderLostpassword(){
        $this::menuFooterLogo();
    $httpRequest = $this->getHttpRequest();
    $ip = $httpRequest->getRemoteAddress();
    $this->template->block = $this->articleModel->block($ip);
    $this->template->ip = $ip;
    }
    /* Formulare */
    /* Prihlaseni */
    protected function createComponentSignForm()
    {
        $form = $this->formFactory->createSignForm();
        $form->onSuccess[] = function (UI\Form $form) {
            $this->redirect('this');exit;
        };
        return $form;
    }
    /* Konec prihlaseni */
    
    /* Registrace form */
    protected function createComponentRegForm()
    {
        $form = $this->formFactory->createRegForm();
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Děkuji za registraci, byl Vám zaslán aktivační email!', 'alert-success');
            $this->redirect('this');exit;
        };
        return $form;
    }
    protected function createComponentRegFormAdmin()
    {
        $form = $this->formFactory->createRegFormAdmin();
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Děkuji za registraci, byl Vám zaslán aktivační email!', 'alert-success');
            $this->redirect('this');exit;
        };
        return $form;
    }
    /* Konec registrace form*/
    /* Obnova hesla */
    protected function createComponentLostForm()
    {
        $form = $this->formFactory->lostForm();
        $form->onSuccess[] = function (UI\Form $form) {
            $this->flashMessage('Obnova hesla proběhla, byl Vám zaslán email s novým heslem!', 'alert-success');
            $this->redirect('Homepage:default');exit;
        };
        return $form;
    }
    /* Konec obnova hesla */
    /* Kontakt shortcode */
    protected function createComponentContactForm(): Form
    {
        $contactRememberValue = $this->getSession('contact');
        $form = new Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->addEmail('email', 'Email:')->setDefaultValue($contactRememberValue->email)->setRequired('Zapoměli jste vyplnit email');
        $form->addTextArea('content', 'Komentář:')->setDefaultValue($contactRememberValue->content)->setRequired('Zapoměli jste vyplnit zprávu');
        $form->addTextArea('antispam', 'Antispam:');
        $form->addHidden('website');
        $form->addSubmit('send', 'Odelast zprávu');
        $form->onValidate[] = function (UI\Form $form, \stdClass $values) {
            $time = DateTime::from(new \DateTime);
            $antispam = $values->antispam;
            $contactRememberValue = $this->getSession('contact');
            $contactRememberValue->email = $values->email;
            $contactRememberValue->setExpiration('5 seconds');
            if($antispam !== $time->format("Y")){ $contactRememberValue->content = $values->content;$this->flashMessage('Chyba ověření, jste robot?', 'alert-danger');$this->redirect('this');exit;}
            $webtime = DateTime::from($values->website);
            $timeTrap = $time->modifyClone('-1 seconds');
            if($webtime >= $timeTrap ){ $this->flashMessage('OPS chyba ověření, zkuste odeslat znovu!', 'alert-warning');$this->redirect('this');exit; }
        };
        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {
            $contactRememberValue = $this->getSession('contact');
            $contactRememberValue->remove();
            $contactEmail = $this->shortCodeModel->getShortCodeOther();
            $this->emailModel->messageTo($contactEmail[1]->contact_email, 'MiRdAFoX');
            $this->emailModel->messageSubject('Kontaktní formulář z webu');
            $this->emailModel->messageContent('<p>Zpráva od '.$values->email.'</p><p>'.$values->content.'</p>');
            $this->emailModel->sendEmail();
            $this->flashMessage('Děkuji za zprávu!', 'alert-success');$this->redirect('this');exit;
        };
        return $form;
    }
    /* Konec contact shortcode */
    /* Formular shortcode */
    protected function createComponentFormForm(): Form
    {
        $form = new UI\Form;
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $inputs = $this->shortCodeModel->getShortCodeOther();
        foreach($inputs as $input){
            foreach ($input->related('form_input') as $formInput) {
                $form->addText($formInput->input,$formInput->label);
            }
        }
        $form->addSubmit('send', 'Odeslat');
        //$form->onValidate[] = [$this, 'processFormValiding'];

        $form->onSuccess[] = function (UI\Form $form, \stdClass $values) {
            $shortcode = $this->shortCodeModel->getShortCodeOther();
            $this->emailModel->messageTo($shortcode[1]->form_email, 'MiRdAFoX');
            $this->emailModel->messageSubject('Formulář z webu '.$shortcode[1]->form_title);
            $content = '';
            foreach($values as $index => $value){
                foreach($shortcode as $valueShorCode){
                    foreach($valueShorCode->related('form_input') as $label){
                        $content .= '<p>'.$label->label.' - '.$value.'</p>';
                    }
                }
            }
            $this->emailModel->messageContent('<h1>'.$shortcode[1]->form_title.'</h1>'.$content);
            $this->emailModel->sendEmail();
            $this->flashMessage("Formulář byl odeslán", 'alert-success');
            $this->redirect('this');
            exit;
        };
        return $form;
    }
    /* Konec formular shortcode */
    /** naseptavac */
    public function handleWhisperer()
    {
        $hledat = $this->getParameter('value');
        if ($this->isAjax()) {
            if(strlen($hledat) <= 2 && strlen($hledat) != 0){ exit; }
            if(strlen($hledat) == 0){
                $result = false;
            }else{
                $result = $this->articleModel->searchPostWhisperer($hledat);
                if(empty($result)){ $result = false; }
            }
            $this->template->whisperer = $result;
            $this->redrawControl('whisperer');
        }
    }

}
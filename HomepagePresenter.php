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
use Nette\Utils\FileSystem;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    protected $factoryFilterMonth;
    protected $factoryFilterSnippetContent;   
    /** @var Nette\Http\Response */
    private $http;
    /** @var FormLogin @inject */
    public $formFactory;
    private $articleModel;
    private $pageModel;
    private $emailModel;

    public function __construct(PageModel $pageModel, ArticleModel $articleModel,Nette\Http\Response $http)
    {
        $this->pageModel = $pageModel;
        $this->articleModel = $articleModel;
        $this->http = $http;
    }

    protected function beforeRender()
    {
	/* overeni prav */
	if (!$this->getUser()->isAllowed('user')) {
	    //throw new Nette\Application\ForbiddenRequestException;
	    $this->redirect('Homepage:sign');exit;
       }
        /** Filter pro ceske mesice a utrzek z textu */
       $this->template->addFilter('czechMonth', new FactoryFilterMonth());
       $this->template->addFilter('snippetContent', new FactoryFilterSnippetContent());
       /** menu */
       $this->template->url = $this->getParameter('url');	   
	   
    }

    public function renderDefault(string $url='')
    {        
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
        /** Logo a footer */
        $logoFooter = $this->pageModel->getLogoFooter();
        $this->template->logo = $logoFooter;
        /** pages */
        $this->template->page = $page;
	$this->template->pageTitle = $page[0]['title'];
        $this->template->pageDescription = $page[0]['description'];
        $this->template->pageKw = $page[0]['kw'];
    }
    public function menuFooterLogo()
    {
        /** Stranka pro prvni zobrazeni, zakladne url je prazdne prvni stranka */
        $pageOrder = $this->pageModel->pageOrderSort();
        /** menu */
        $this->template->menus = $pageOrder;
        /** Logo a footer */
        $this->template->logo = $this->pageModel->getLogoFooter();
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
    /** aktivace */
    public function renderActive(){

        /** z bezpecnostnich důvodů neuvádím */
        
    }
    /** registrace */
    public function renderRegistration()
    {
        $this::menuFooterLogo();
        $httpRequest = $this->getHttpRequest();
       /** z bezpecnostnich důvodů neuvádím */
    }
    /** Prihlaseni */
    public function renderSign(){
        $this::menuFooterLogo();
        $httpRequest = $this->getHttpRequest();
        /** z bezpecnostnich důvodů neuvádím */
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
    /** naseptavac */
    public function handleWhisperer()
    {
        $search = $this->getParameter('value');
        if ($this->isAjax()) {
            if(strlen($search) <= 2 && strlen($search) != 0){ exit; }
            if(strlen($search) == 0){
                $result = false;
            }else{
                $result = $this->articleModel->searchPostWhisperer($search);
                if(empty($result)){ $result = false; }
            }
            $this->template->whisperer = $result;
            $this->redrawControl('whisperer');
        }
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 15/02/16
 * Time: 12:27
 */

namespace Legrain\ApiBundle\Services;



use AppBundle\Entity\AccountBalance;
use AppBundle\Entity\AccountBalanceLine;
use AppBundle\Entity\Bugzilla;
use AppBundle\Entity\Cart;
use AppBundle\Entity\CartLine;
use AppBundle\Entity\Contact;
use AppBundle\Entity\DataCenter;
use AppBundle\Entity\EmailGandiPackPro;
use AppBundle\Entity\Hosting;
use AppBundle\Entity\Instance;
use AppBundle\Entity\ListBugBugzilla;
use AppBundle\Entity\Log;
use AppBundle\Entity\Ndd;
use AppBundle\Entity\NextPaiement;
use AppBundle\Entity\PriceList;
use AppBundle\Entity\PriceListLine;
use AppBundle\Entity\ProductHosting;
use AppBundle\Entity\ResponderEmail;
use AppBundle\Entity\SnapshotProfileInstance;
use AppBundle\Entity\TmpRenewPackMail;
use AppBundle\Entity\User;
use AppBundle\Entity\Vhosts;
use AppBundle\Soap\Entity\Category;
use AppBundle\Soap\Entity\CGU;
use AppBundle\Soap\Entity\City;
use AppBundle\Soap\Entity\ContactInfos;
use AppBundle\Soap\Entity\EmailInfos;
use AppBundle\Soap\Entity\Feature;
use AppBundle\Soap\Entity\InstanceSnapshot;
use AppBundle\Soap\Entity\Product;
use AppBundle\Soap\Entity\ProductSimplified;
use AppBundle\Soap\Entity\SnapshotProfile;
use AppBundle\Soap\Entity\WebRedir;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Agency;
use AppBundle\Soap\Entity\TiersPourTVA;
use AppBundle\Soap\Entity\ZipCode;
use AppBundle\Soap\Security\UserSecurity;
use GandiBundle\Controller\GandiController;
use GandiBundle\ThirdParty\XmlRpcGandi;
use Legrain\ToolsBundle\Service\CurlBugzilla;
use Legrain\ToolsBundle\Service\Math;
use Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpKernel\Kernel;

class GwiHostingService
{

    protected $em;
    protected $math;
    protected $user;
    protected $logger;
    protected $mailer;

    protected $email_app;
    protected $twig;
    protected $stripe_key;
    protected $curlBugzilla;
    protected $id_changelog_gwi;
    protected $id_changelog_gwi_utilisateur_agence_web;
    protected $wsdl_changelog;

    protected $identifiant_gandi_admin;
    protected $identifiant_gandi_bill;
    protected $identifiant_gandi_tech;
    protected $email_gandi_per_default;
    protected $email_notification_paiement;

    protected $email_notification_inscription;

    public function __construct(EntityManager $em, Math $math, CurlBugzilla $curlBugzilla, Logger $logger, \Swift_Mailer $mailer,
                                TwigEngine $twig, $email_app, $stripe_key, $id_changelog_gwi, $id_changelog_gwi_utilisateur_agence_web, $wsdl_changelog,
                                $identifiant_gandi_admin, $identifiant_gandi_bill, $identifiant_gandi_tech, $email_gandi_per_default, $email_notification_paiement,$email_notification_inscription
    )
    {
        $this->em = $em;
        $this->math = $math;
        $this->user = null;
        $this->logger = $logger;
        $this->logger->info('Instanciation du service GWI hosting');
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->email_app = $email_app;
        $this->stripe_key = $stripe_key;
        $this->curlBugzilla = $curlBugzilla;
        $this->id_changelog_gwi = $id_changelog_gwi;
        $this->id_changelog_gwi_utilisateur_agence_web = $id_changelog_gwi_utilisateur_agence_web;
        $this->wsdl_changelog = $wsdl_changelog;

        $this->identifiant_gandi_admin = $identifiant_gandi_admin;
        $this->identifiant_gandi_bill = $identifiant_gandi_bill;
        $this->identifiant_gandi_tech = $identifiant_gandi_tech;
        $this->email_gandi_per_default = $email_gandi_per_default;
        $this->email_notification_paiement = $email_notification_paiement;
        $this->email_notification_inscription = $email_notification_inscription;


    }

    /**
     * @param string $username
     * @param string $password
     * @return string (json)
     * @throws \SoapFault
     */
    public function nbAchatEtRenouvellementParMois($username, $password)
    {
        $userConnected = $this->login($username, $password);

        $cartLinesRepository = $this->em->getRepository('AppBundle:CartLine');

        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $d = date('Y');
        $date = new \DateTime($d . '-01-01');

        $listAchatNdd = $cartLinesRepository->findLinesPerProductCategoryAndUser($productCategoryRepository->findOneByName('createndd'), $userConnected, $date);
        $listAchatServer = $cartLinesRepository->findLinesPerProductCategoryAndUser($productCategoryRepository->findOneByName('instance'), $userConnected, $date);
        $listRenewNdd = $cartLinesRepository->findLinesPerProductCategoryAndUser($productCategoryRepository->findOneByName('renewndd'), $userConnected, $date);
        $listRenewServer = $cartLinesRepository->findLinesPerProductCategoryAndUser($productCategoryRepository->findOneByName('renewinstance'), $userConnected, $date);


        $return = array();
        for ($i = 1; $i <= 12; $i++) {
            $return[$i]['createndd'] = 0;
            $return[$i]['renewndd'] = 0;
            $return[$i]['instance'] = 0;
            $return[$i]['renewinstance'] = 0;
        }
        foreach ($listAchatNdd as $achat) {
            $return[(int)$achat->getWhenUpdate()->format('m')]['createndd']++;
        }
        foreach ($listAchatServer as $achat) {
            $return[(int)$achat->getWhenUpdate()->format('m')]['instance']++;
        }
        foreach ($listRenewNdd as $achat) {
            $return[(int)$achat->getWhenUpdate()->format('m')]['renewndd']++;
        }
        foreach ($listRenewServer as $achat) {
            $return[(int)$achat->getWhenUpdate()->format('m')]['renewinstance']++;
        }

        return json_encode($return);

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idContact
     * @return \AppBundle\Soap\Entity\ContactInfos
     * @throws \SoapFault
     */
    public function getInfosContact($username, $password, $idContact)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $contactRepository = $this->em->getRepository('AppBundle:Contact');


        $contact = $contactRepository->find($idContact);
        if ($contact == null) throw new \SoapFault('error', 'Ce contact n\'existe pas ou plus');
        try {
            $this->checkAuthorisations($userConnected, $contact->getUser());
        } catch (\SoapFault $e) {
            throw new \SoapFault('e', $e->getMessage());
        }

        $u = $contact->getUser();
        $city = new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
        $zipcode = new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());
        //$agency = new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()), $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(),$u->getAgency()->getFacturationBylegrain(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement());
        $agency = $this->_getAgency($u->getAgency());
        $usr = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(), new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName()));
        return new ContactInfos($contact->getId(), $contact->getFakeEmail(), $contact->getIsDefault(), $usr, $contact->getCode());
    }


    /**
     * @param string $urlApp
     * @param string $category
     * @return \AppBundle\Soap\Entity\Product[]
     * @throws \SoapFault
     */
    public function publicPricesProductsPerCategory($urlApp, $category)
    {
        $agency = $this->_getAgencyPerUrlApp($urlApp);


        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $productRepository = $this->em->getRepository('AppBundle:Product');

        // On charge tous les produits qui appartiennent à cette catégorie
        $dCategory = $productCategoryRepository->findOneByName($category);

        $products = $productRepository->findByCategoryAndActive($dCategory, true);

        $return = array();


        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');

        $productAgencyRepository = $this->em->getRepository('AppBundle:ProductAgency');

        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        foreach ($products as $product) {

            // On cherche le prix pour la liste de l'agence.
            $priceListAgency = $priceListRepository->findOneBy(array('isDefault' => true, 'parentAgency' => $agency));

            $priceListApplicationDefault = $priceListRepository->findOneByIsApplicationDefault(true);

            $priceDefined = false;
            $priceListLine = null;

            if ($priceListAgency != null && !$priceDefined) {
                $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListAgency));
                if ($priceListLine) $priceDefined = true;
            }
            if ($priceListApplicationDefault != null && !$priceDefined) {
                $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListApplicationDefault));
                if ($priceListLine) $priceDefined = true;
            }


            if ($priceListLine == null) throw new \SoapFault('error', 'Aucune règle de prix spécifiée pour le produit : ' . $product->getName());

            $productAgency = $productAgencyRepository->findOneByAgency($agency);

            $codeFacturation = $productAgency ? $productAgency->getCodeFacturation() : '';

            $features = array();
            foreach ($product->getFeaturesAsArray() as $key => $value) {
                $features[] = new Feature($key, $value);
            }

            $return[] = new \AppBundle\Soap\Entity\Product(
                $product->getId(), $product->getName(), $product->getReference(), $product->getCodeLgr(), $product->getShortDescription(), $product->getLongDescription(), $product->getMinPeriod(),
                $codeFacturation, $priceListLine->getPrice(), $priceListLine->getMinPrice(),
                $priceListLine->getTvaRate()->getPercent()
                , null, null, null, null, $features, $product->getActive(), null
            );
        }

        return $return;
    }

    /**
     * Charge les prix publics en fonction du nom de la catégorie
     * @param string $username
     * @param string $password
     * @param string $category
     * @return \AppBundle\Soap\Entity\Product[]
     * @throws \SoapFault
     */
    public function publicPricesProductsPerCategoryAndUser($username, $password, $category)
    {
        $user = $this->login($username, $password);


        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $productRepository = $this->em->getRepository('AppBundle:Product');

        // On charge tous les produits qui appartiennent à cette catégorie
        $dCategory = $productCategoryRepository->findOneByName($category);

        $products = $productRepository->findByCategoryAndActive($dCategory, true);

        $return = array();


        foreach ($products as $product) {
            $return[] = $this->getProduct($username, $password, $product->getId(), $user->getId());
        }

        return $return;
    }


    private function _getAgencyPerUrlApp($urlApp)
    {
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agency = $agencyRepository->findOneByUrlApp($urlApp);
        // Si pas trouvé, on charge la premiere ( legrain)
        if (!$agency) $agency = $agencyRepository->find(1);
        return $agency;
    }

    /**
     * Retourne l'agence correspondante à une url. Si l'url n'est pas trouvée, l'agence de legrain sera chargée
     * @param String $urlApp
     * @return \AppBundle\Soap\Entity\Agency
     */
    public function getAgencyPerUrlApp($urlApp){
        return $this->_getAgency($this->_getAgencyPerUrlApp($urlApp));
    }


    /**
     * Retourne un Json donnant l'etat du ndd
     * @param string $domain
     * @return string
     */
    public function testPublishedTldsAvailable($domain)
    {
        // On charge la catégorie ndd
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');

        $nddCat = $productCategoryRepository->findOneByName('createndd');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        //$tlds = $productRepository->findBy(array('categories'=>$nddCat,'active'=>true));
        $tlds = $productRepository->findByCategoryAndActive($nddCat, true);

        $domainExplosed = explode('.', $domain);

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        $return = array();
        foreach ($tlds as $tld) {
            $completeName = $domainExplosed[0] . $tld->getFeatures()->tld;
            $tmp = array();
            $tmp['domain'] = $completeName;
            $tmp['availlable'] = $gandiApi->domainAvaillable($connect, $completeName);
            $tmp['idproduct'] = $tld->getId();

            $return[] = $tmp;
        }
        return json_encode($return);
    }

    /**
     * Retourne un json indiquant si le domaine est disponible
     * @param string $domain
     * @param string $tld
     * @return string
     * @throws \SoapFault
     */
    public function testDomainAvailable($domain, $tld)
    {
        if (substr($tld, 0, 1) == '.') $tld = substr($tld, 1);
        // On charge la catégorie ndd
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $nddCat = $productCategoryRepository->findOneByName('createndd');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        //$tlds = $productRepository->findBy(array('categories'=>$nddCat,'active'=>true));
        // $tlds = $productRepository->findByCategoryAndActive($nddCat,true);

        $dTld = $productRepository->findOneByTldAndCategory($tld, $nddCat);

        if ($dTld == null) throw new \SoapFault('e', 'TLD inconnu');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


        $completeName = $domain . '.' . $tld;
        $return = array();
        $return['domain'] = $completeName;
        $return['availlable'] = $gandiApi->domainAvaillable($connect, $completeName);
        $return['idproduct'] = $dTld->getId();


        return json_encode($return);
    }

    /**
     * Retourne un json indiquant si les extensions sont disponibles pour ce  domaine
     * @param string $domain
     * @param string $listids
     * @return string
     * @throws \SoapFault
     */
    public function testListDomainsAvailables($domain, $listids)
    {
        $listids=explode(';',$listids);
        // On charge la catégorie ndd
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $nddCat = $productCategoryRepository->findOneByName('createndd');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        //$tlds = $productRepository->findBy(array('categories'=>$nddCat,'active'=>true));
        // $tlds = $productRepository->findByCategoryAndActive($nddCat,true);

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        $return = array();
        $domains = array();
        $infos = array();
        foreach($listids as $id){

            $dTld = $productRepository->find($id);

            if ($dTld == null) throw new \SoapFault('e', 'TLD inconnu 1');

            if(!$dTld->getCategories()->contains($nddCat))throw new \SoapFault('e', 'TLD inconnu 2');



            $tld = $dTld->getFeatures()->tld;
            $domains[] =  $domain  . $tld;
            $infos[$domain  . $tld]=$id;

            /*
            $completeName = $domain  . $tld;

            $tmp = array();
            $tmp['domain'] = $completeName;
            $tmp['id'] = $id;
            $tmp['availlable'] = $gandiApi->domainAvaillable($connect, $completeName);
            $return[]=$tmp;
*/
        }

        $res = $gandiApi->domainsAvaillable($connect, $domains);

        foreach($res as $key => $value){
            $tmp = array();

            $tmp['domain'] = $key;
            $tmp['id'] = $infos[$key];
            if($value =='pending'){
                $tmp['availlable'] = $gandiApi->domainAvaillable($connect, $key);
            }else{
                $tmp['availlable'] = $key=='available'?true:false;
            }
            $return[]=$tmp;
        }



        return json_encode($return);
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idContact
     * @param string $jsonParameters
     * @return bool
     * @throws \SoapFault
     */
    public function updateContact($username, $password, $idContact, $jsonParameters)
    {


        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $contactRepository = $this->em->getRepository('AppBundle:Contact');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $rolelegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');

        $contact = $contactRepository->find($idContact);
        if ($contact == null) throw new \SoapFault('error', 'Ce contact n\'existe pas ou plus');
        try {
            $this->checkAuthorisations($userConnected, $contact->getUser());
        } catch (\SoapFault $e) {
            throw new \SoapFault('e', $e->getMessage());
        }


        $item = json_decode($jsonParameters);
        if ($userConnected->getRoles()->contains($rolelegrain) || $userConnected->getRoles()->contains($roleAgence)) {
            if (property_exists($item, 'iduser')) $contact->setUser($userRepository->find($item->iduser));
        }

        if (property_exists($item, 'isDefault')) {
            if ($item->isDefault) {
                // On met par défaut à faux pour tous les contacts de cet utilisateur
                $this->em->createQuery('UPDATE AppBundle:Contact c set c.isDefault = true WHERE c.isDefault= true AND c.user = :user')->setParameter('user', $userConnected)->execute();
            }
            $contact->setIsDefault($item->isDefault);
        }
        if (property_exists($item, 'fakeEmail')) $contact->setFakeEmail($item->fakeEmail);

        $this->em->persist($contact);
        $this->em->flush();

        return true;

    }


    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\Contact[]
     * @throws \SoapFault
     */
    public function listAllContacts($username, $password)
    {
        $userConnected = $this->login($username, $password);
        $contactRepository = $this->em->getRepository('AppBundle:Contact');

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $rolelegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($userConnected->getRoles()->contains($rolelegrain)) {
            $listContacts = $contactRepository->findAll();
        } elseif ($userConnected->getRoles()->contains($roleAgence)) {
            $listContacts = $contactRepository->findForAgency($userConnected->getAgency());
        } else {
            $listContacts = $contactRepository->findByUser($userConnected);
        }

        $contacts = array();
        foreach ($listContacts as $contact) {
            $contacts[] = new \AppBundle\Soap\Entity\Contact($contact->getId(), $contact->getEmail(), $contact->getFakeEmail(), $contact->getCode(), $contact->getIsDefault(), $contact->getName(), $contact->getFirstname(), ($contact->getUser() ? $contact->getUser()->getCodeClient() : null));
        }
        return $contacts;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idCart
     * @param int $idLine
     * @param int $idNewUser
     * @return bool
     * @throws \SoapFault
     */
    public function changeUserToLineInCart($username, $password, $idCart, $idLine, $idNewUser)
    {

        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $userConnected = $this->login($username, $password);

        $cart = $cartRepository->find($idCart);
        if (!$cart) throw new \SoapFault('e', 'Ce panier n\'existe pas ou plus');
        if ($cart->getUser()->getId() !== $userConnected->getId()) {
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
            if (!$userConnected->getRoles()->contains($roleAgence) && $cart->getUser()->getAgency()->getId() !== $userConnected->getAgency()->getId())
                throw new \SoapFault('e', 'Impossible de modifier ce panier car il ne vous appartient pas');
        }

        if ($cart->getIsPaid()) throw new \SoapFault('e', 'Impossible de modifier ce panier car il est déjà réglé');
        $line = $cartLineRepository->find($idLine);
        if (!$line) throw new \SoapFault('e', 'Cette ligne n\'existe pas ou plus');
        if ($line->getCart()->getId() != $cart->getId()) throw new \SoapFault('e', 'La ligne n\'appartient pas à ce panier');

        $newUser = $userRepository->find($idNewUser);
        $options = json_decode($line->getOptions());
        if (!property_exists($options, 'lineInCart')) $line->setUtilisateurPourLequelEstLeProduit($newUser);
        $this->em->persist($line);
        $this->em->flush();
        return true;
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idCart
     * @param string $jsonContacts
     * @return bool
     * @throws \SoapFault
     */
    public function addContactsToCartLines($username, $password, $idCart, $jsonContacts)
    {

        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $contactRepository = $this->em->getRepository('AppBundle:Contact');

        $userConnected = $this->login($username, $password);

        $cart = $cartRepository->find($idCart);
        if (!$cart) throw new \SoapFault('e', 'Ce panier n\'existe pas ou plus');
        if ($cart->getUser()->getId() !== $userConnected->getId()) {
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
            if (!$userConnected->getRoles()->contains($roleAgence) && $cart->getUser()->getAgency()->getId() !== $userConnected->getAgency()->getId())
                throw new \SoapFault('e', 'Impossible de modifier ce panier car il ne vous appartient pas');
        }
        if ($cart->getIsPaid()) throw new \SoapFault('e', 'Impossible de modifier ce panier car il est déjà réglé');
        $nameContacts = json_decode($jsonContacts);
        if (empty($nameContacts)) throw new \SoapFault('e', 'Vous devez spécifier au moins un contact');

        foreach ($nameContacts as $idLine => $nameContact) {
            // on charge la ligne
            $line = $cartLineRepository->find($idLine);
            if (!$line) throw new \SoapFault('e', 'Cette ligne n\'existe pas ou plus');
            if ($line->getCart()->getId() != $cart->getId()) throw new \SoapFault('e', 'La ligne n\'appartient pas à ce panier');
            // On charge le contact
            $contact = $contactRepository->findOneByCode($nameContact);
            if (!$contact) throw new \SoapFault('e', 'Ce contact n\'existe pas ou plus.');
            $options = json_decode($line->getOptions());
            $options->contact = $contact->getCode();
            $options->valid = true;
            $line->setOptions(json_encode($options));
            if ($contact->getUser() != null && !property_exists($options, 'lineInCart')) $line->setUtilisateurPourLequelEstLeProduit($contact->getUser());
            $this->em->persist($line);
        }
        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\NextPaiement[]
     * @throws \SoapFault
     */
    public function listProductsNextPayement($username, $password)
    {
        $userConnected = $this->login($username, $password);
        $nextPaiementRepository = $this->em->getRepository('AppBundle:NextPaiement');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');

        // Si pas gestionnaire. On le vire
        if (!$userConnected->getRoles()->contains($roleAgency)) throw new \SoapFault('e', 'Accès interdit');
        $agency = $userConnected->getAgency();

        $list = $nextPaiementRepository->findBy(array('agency' => $agency, 'isArchived' => false, 'inCart' => false));

        $return = array();
        foreach ($list as $item) {
            $product = $this->getProduct($username, $password, $item->getProduct()->getId(), $userConnected->getId());
            $user = $item->getUserFinal();
            $return[] = new \AppBundle\Soap\Entity\NextPaiement(
                $item->getId(),
                $item->getDate(),
                $item->getReference(),
                $item->getName(),
                $item->getQuantity(),
                $item->getUnitPriceHt(),
                $item->getPercentTax(),
                $item->getTotalHT(),
                $item->getTotalTax(),
                $item->getFeatures(),
                null,
                $product,
                new \AppBundle\Soap\Entity\User(
                    $user->getId(),
                    $user->getName(),
                    $user->getFirstname(),
                    $user->getEmail(),
                    $user->getAddress1(),
                    $user->getAddress2(),
                    $user->getAddress3(),
                    new \AppBundle\Soap\Entity\City($user->getCity()->getId(), $user->getCity()->getName(), $user->getCity()->getCodeInsee()),
                    new \AppBundle\Soap\Entity\ZipCode($user->getZipcode()->getId(), $user->getZipcode()->getName()),
                    $user->getPhone(),
                    $user->getActive(),
                    null,
                    null,
                    $user->getCellPhone(),
                    $user->getWorkPhone(),
                    $user->getCompanyName(),
                    $user->getCodeClient(),
                    $user->getNumTVA(),
                    null


                )

            );
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @param bool $acceptCgu
     * @return bool
     * @throws \SoapFault
     */
    public function payCart($username, $password, $iduser, $acceptCgu){
        try {
            if ($acceptCgu == false) throw new \SoapFault('error', 'Vous devez accepter les conditions générales de ventes');

            $cartRepository = $this->em->getRepository('AppBundle:Cart');
            $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
            $userRepository = $this->em->getRepository('AppBundle:User');

            // Reste on ajoute produits
            $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');

            $userConnected = $this->login($username, $password);

            $wsCart = $this->getCart($username, $password, $userConnected->getId());
            $cart = $cartRepository->find($wsCart->id);
            // Vérification

            // Je dois vérifier si le panier possède des créations de domaine et, si l'option peutEtreReserve est à true. Le mettre en erreur avant tout traitement si c'est le cas
            $productNddCreate = $productCategoryRepository->findOneByName('createndd');
            $test = $cartLineRepository->findLinesPerProductCategoryAndOptionValidIsTrue($cart, $productNddCreate);
            if (!empty($test)) throw new \SoapFault('e', 'Impossible de payer ce panier car certaines noms de domaines n\'ont pas été associés à un contact valide');
            $error = true;
            // Si iduser est dans la liste des payeurs potentiels de ce panier
            $users = $wsCart->potentialPayer;
            foreach ($users as $u) {
                if ($u->id = $iduser) {
                    $error = false;
                }
            }
            if ($error) throw new \SoapFault('error', 'Accès non autorisé');
            $userWhoPaid = $userRepository->find($iduser);
            $accountBalance = $userWhoPaid->getAccountBalance();
            /*
                    // Recalcul de toutes les lignes dans le cas ou l'utilidsateur qui paye n'est pas un gestionnaire et que l'agence (sauf facture) n'est pas assujetti à la tva
                    $agencyUserWhoPaid = $userWhoPaid->getAgency();
                    if ($userWhoPaid->getParent() != null && !$agencyUserWhoPaid->getFacturationBylegrain() && !$agencyUserWhoPaid->getUseTva()) {
                        foreach ($cart->getCartLines() as $cartLine) {
                            $cartLine->setTotalTax(0.00);
                            $cartLine->setPercentTax(0.00);
                            $cart->setTotalTax(0);
                            $this->em->persist($cartLine);
                            $this->em->persist($cart);
                        }
                    }
            */
            $newPriceTotal = json_decode($this->getTotalCartPerUserWhoPaid($username, $password, $cart->getId(), $userWhoPaid->getId()));
            if (((float)$newPriceTotal->totalTTC - (float)$accountBalance->getAmount()) > 0) throw new \SoapFault('error', 'Solde insufisant ');


            $mouvement = new AccountBalanceLine();
            $mouvement->setMouvement(-$newPriceTotal->totalTTC);
            $mouvement->setCart($cart);
            $mouvement->setDescription('Relatif au panier ' . $cart->getId());

            $accountBalance->addLine($mouvement);

            $cart->setIsPaid(true);
            // On réattribue le panier à l'utilisateur qui paye

            $cart->setDateIsPaid(new \DateTime());
            $this->em->persist($mouvement);

            // On traite le cas du produit hébergement

            // traitement du panier en fct de qui paye.
            // Si le payeur a son compte prépayé associé à legrain. Si ce n'est pas le cas. On doit l'ajouter aux paiement en attente de son gestionnaire. Ou si facturationParLegrain
            // if user->getAgency->getId ==1 OU iuser->getParent ==null
            //if($userWhoPaid->getId()==$cart->getUSer()->getId()) {
            if ($userWhoPaid->getParent() == null || $userWhoPaid->getAgency()->getId() == 1 || $userWhoPaid->getAgency()->getFacturationBylegrain()) {
                $cart = $this->traitementFinalPanier($cart, $userConnected, $userWhoPaid, $productNddCreate, $mouvement, $username, $password);
            } else {

                // Si le gestionnaire a les paiement par carte auto (n'existe pas pour le moment)
                // Du coup, traitement "classique2" :
                //- On traite le panier et on enregistre chaque ligne dans une table "traitement en attente".
                // - Cette liste pourra être ajouté au panier du gestionnaire.
                // A la validation de ce nouveau panier, on devra ajouter les produits aux membres finaux.

                foreach ($cart->getCartLines() as $line) {
                    $product = $line->getProduct();
                    $pro2 = $this->getProduct($username, $password,$product->getId() , $userWhoPaid->getId());

                    // $product,$options,User $userConnected,$username,$password,$iduserRequest,$libel=''
                    $newPrice = json_decode($this->calculPriceLine($pro2, json_decode($line->getOptions()), $userConnected, $username, $password, $userWhoPaid->getId()));
                    $cart->setTotalTax($cart->getTotalTax()-$line->getTotalTax()+$newPrice->totalTax);
                    $cart->setTotalHt($cart->getTotalHt()-$line->getTotalHt()+$newPrice->totalHt);
                    $this->em->persist($cart);
                    $line->setTotalHt($newPrice->totalHt);
                    $line->setTotalTax($newPrice->totalTax);
                    $line->setUnitPrice($newPrice->unitPrice);
                    $line->setPercentTax($newPrice->percentTax);

                    $this->em->persist($line);
                    if ($product->getReference() != 'produit_generique_instance_mutualisable') {
                        $ligneEnAttente = new NextPaiement();
                        $ligneEnAttente->setDate(new \DateTime());
                        $ligneEnAttente->setClientCart($cart);
                        $ligneEnAttente->setProduct($product);
                        $ligneEnAttente->setName($line->getProductName());
                        $ligneEnAttente->setReference($line->getProductReference());
                        $ligneEnAttente->setPercentTax($line->getPercentTax());
                        $ligneEnAttente->setQuantity($line->getQuantity());
                        $ligneEnAttente->setTotalHT($line->getTotalHt());
                        $ligneEnAttente->setUnitPriceHt($line->getUnitPrice());
                        $ligneEnAttente->setTotalTax($line->getTotalTax());
                        $ligneEnAttente->setFeatures($line->getOptions());
                        $ligneEnAttente->setUserFinal($line->getUtilisateurPourLequelEstLeProduit());
                        // On doit vérifier que l'utilisateur n'est pas un gestionnaire. Si c'est le cas, on charge l'agence 1 ( legrain)
                        $ligneEnAttente->setAgency($userWhoPaid->getParent() == null ? $this->em->getRepository('AppBundle:Agency')->find(1) : $userWhoPaid->getAgency());
                        $ligneEnAttente->setIsArchived(false);
                        $this->em->persist($ligneEnAttente);
                    } else {
                        // Si produit hébergement mutualisé
                        $this->traitementCartLineProduitHebergementMutualise($line, $userConnected, $userWhoPaid, $mouvement);
                    }
                }
            }
            $cart->setUser($userWhoPaid);
            $this->em->persist($cart);
            $this->em->flush();

            // Envoi du mail à legrain contenant le contenu du panier. Ainsi que le détail de l'utilisateur.
            $message = \Swift_Message::newInstance()
                ->setSubject('Détail de la commande relative au panier : ' . $cart->getId())
                ->setFrom($this->email_app)
                ->setTo($this->email_notification_paiement)
                ->setBody(
                    $this->twig->render(
                    // app/Resources/views/Emails/registration.html.twig
                        'Email/cart.email.html.twig',
                        array('cart' => $cart,'email'=>$userConnected->getEmail())
                    ),
                    'text/html'
                );


            $this->mailer->send($message);

            return true;
        }catch(\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }

    private function traitementCartLineProduitHebergementMutualise(CartLine $cartLine, User $userConnected, User $userWhoPaid, AccountBalanceLine $mouvement)
    {
        try {

            $product = $cartLine->getProduct();
            if ($product->getReference() == 'produit_generique_instance_mutualisable') {

                // Action.
                $options = json_decode($cartLine->getOptions());
                $productHosting = $this->em->getRepository('AppBundle:ProductHosting')->find($options->idProduitHeber);
                // On ajoute une ligne au panier recredita,t du prix ttc de la ligne + idem sur le mouvement.
                /* Remboursement si :
                - Gestionnaire = utilisateur utilisant api
                - Gestionnaire paye
                - gestionnaire d'une agence qui facture (ou legrain)
                */
                if (
                    $userConnected->getParent() == null &&
                    $userConnected->getId() == $userWhoPaid->getId() &&
                    ($userConnected->getAgency()->getFacturationBylegrain() == false || $userConnected->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN')))
                ) {
                    // Si c'est pas legrain et que le produit n'appartient pas à l'agence. On ne fait rien non plus/
                    /*
                     $productHosting = $this->em->getRepository('AppBundle:ProductHosting')->find($options->idProduitHeber);
                     * Si pas legrain
                     !$userConnected->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN'))
                        AND
                        $productHosting->getAgency()->getId() == $userConnected->getAgency()->getId()
                     */
                    if (
                        ($userConnected->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN'))) ||
                        (!$userConnected->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN')) && ($productHosting->getAgency()->getId() == $userConnected->getAgency()->getId()))
                    ) {
                        $mouvement->setMouvement($mouvement->getMouvement() + $cartLine->getTotalTTC());
                        $this->em->persist($mouvement);
                        $cart = $cartLine->getCart();
                        $cart->setTotalHt($cart->getTotalHt() - $cartLine->getTotalHt());
                        $cart->setTotalTax($cart->getTotalTax() - $cartLine->getTotalTax());
                        $cartLine->setTotalHt(0.00);
                        $cartLine->setTotalTax(0.00);

                        $this->em->persist($cartLine);
                        $this->em->persist($cart);
                    }
                }


                if (property_exists($options, 'idHosting')) {
                    // upd
                    $hosting = $this->em->getRepository('AppBundle:Hosting')->find($options->idHosting);
                    $dateEnd = $hosting->getDateEnding();
                    $dateEnd->add(new \DateInterval('P' . $options->period . 'M'));
                    $hosting->setDateEnding(clone $dateEnd);
                    $this->em->persist($hosting);
                } else {
                    $dateEnd = new \DateTime();
                    $dateEnd->add(new \DateInterval('P' . $options->period . 'M'));
                    $hosting = new Hosting();
                    $hosting->setProductHosting($productHosting);
                    $hosting->setDateEnding($dateEnd);
                    $hosting->setUser($cartLine->getUtilisateurPourLequelEstLeProduit());
                    $this->em->persist($hosting);
                }
                $this->em->flush();
            }
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }
    }

    private function traitementFinalPanier($cart, $userConnected, $userWhoPaid, $productNddCreate, AccountBalanceLine $mouvement,$username,$password)
    {

        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $renewNddCategory = $productCategoryRepository->findOneByName('renewndd');
        $renewInstanceCategory = $productCategoryRepository->findOneByName('renewinstance');
        $sizeInstanceCategory = $productCategoryRepository->findOneByName('puissanceInstance');


        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $serviceProviderRepository = $this->em->getRepository('AppBundle:ServiceProvider');
        $emailGandiPackProRepository = $this->em->getRepository('AppBundle:EmailGandiPackPro');
        $sizeInstanceRepository = $this->em->getRepository('AppBundle:SizeInstance');
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $createInstanceCategory = $productCategoryRepository->findOneByName('instance');

        $snapshotProfileInstanceRepository = $this->em->getRepository('AppBundle:SnapshotProfileInstance');

        // On charge la catégorie "optionPartHddInstance"
        $optionPartHddInstanceCategory = $productCategoryRepository->findOneByName('optionPartHddInstance');

        $nbreVhostsInstanceCategory = $productCategoryRepository->findOneByName('Nombre_sites_maxi_par_instance');

        $instanceSauvegardeAutoCategory = $productCategoryRepository->findOneByName('instance_sauvegarde_auto');

        $produitHebergementCategory = $productCategoryRepository->findOneByName('produit_generique_instance_mutualisable');


        // traitement chez Gandi (entre autre) sous certaines conditions.


        foreach ($cart->getCartLines() as $line) {
            $product = $line->getProduct();

            //$username, $password, $idProduct, $iduser
            $pro2 = $this->getProduct($username, $password,$product->getId() , $userWhoPaid->getId());

            // $product,$options,User $userConnected,$username,$password,$iduserRequest,$libel=''

            $newPrice = json_decode($this->calculPriceLine($pro2, json_decode($line->getOptions()), $userConnected, $username, $password,$userWhoPaid->getId() ));

            $cart->setTotalTax($cart->getTotalTax()-$line->getTotalTax()+$newPrice->totalTax);
            $cart->setTotalHt($cart->getTotalHt()-$line->getTotalHt()+$newPrice->totalHt);
            $this->em->persist($cart);

            $line->setTotalHt($newPrice->totalHt);
            $line->setTotalTax($newPrice->totalTax);
            $line->setUnitPrice($newPrice->unitPrice);
            $line->setPercentTax($newPrice->percentTax);

            $this->em->persist($line);


            switch ($product->getId()) {

                // Ajout du produit ( EmailGandiPackPro )
                case 1:
                    $options = json_decode($line->getOptions());

                    $emailPackPro = new EmailGandiPackPro();
                    $emailPackPro->setSize(1);
                    $emailPackPro->setUser((property_exists($options, 'idUser') ? $options->idUser : $line->getUtilisateurPourLequelEstLeProduit()));
                    $ndd = $nddRepository->findOneByName($options->ndd);
                    $emailPackPro->setDateEnding($ndd->getExpirationDate());
                    $emailPackPro->setNdd($ndd);
                    // 1 = gandi;
                    $emailPackPro->setServiceProvider($serviceProviderRepository->find(1));

                    // Appel Gandi

                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';
                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

                    $log = new Log($userConnected, 'Ajout du pack mailpro sur le domaine : ' . $ndd->getName());

                    $this->em->persist($log);

                    $durationInDay = $this->math->nombreDeJours(date('Y-m-d'), $ndd->getExpirationDate()->format('Y-m-d'));
                    $gandiApi->createPackMail($connect, $ndd->getName(), $durationInDay);

                    $this->em->persist($emailPackPro);
                    break;
                // Update du produit ( EmailGandiPackPro )
                case 2:
                    $options = json_decode($line->getOptions());
                    // On récupère le packpro
                    $ndd = $nddRepository->findOneByName($options->ndd);
                    $emailPackPro = $emailGandiPackProRepository->findOneByNdd($ndd);
                    $emailPackPro->setSize($options->size);


                    // Appel Gandi

                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

                    $log = new Log($userConnected, 'Modification pack mail pro du domaine : ' . $ndd->getName());

                    $this->em->persist($log);

                    // date de fin du domaine et de fin du packmail
                    $dateEndindDomain = $ndd->getExpirationDate();
                    $dateEndingPackMail = $emailPackPro->getDateEnding();
                    // Si la date de fin du domaine est > à celle du pack mail, on inscrit le pack dans la table servant à renouveler.
                    if ($dateEndindDomain->getTimestamp() > $dateEndingPackMail->getTimestamp()) {
                        $newLineRenew = new TmpRenewPackMail();
                        $newLineRenew->setNdd($ndd);
                        $newLineRenew->setPackMail($emailPackPro);
                        $newLineRenew->setDateShouldBeTheDomain($ndd->getExpirationDate());
                        $this->em->persist($newLineRenew);
                    }
                    if ($options->size == 0) {
                        // Si la taille est = à 0, on supprime le pack mail.
                        $gandiApi->removePackMail($connect, $ndd->getName());
                        $this->em->remove($emailPackPro);
                    } else {
                        $gandiApi->updatePackMail($connect, $ndd->getName(), $options->size);
                        $this->em->persist($emailPackPro);
                    }
                    break;
                // case produit appartenant à la catégorie "optionPartHddInstance"
                case $product->getCategories()->contains($optionPartHddInstanceCategory):
                    $options = json_decode($line->getOptions());
                    // Instance sur laquelle la puissance est modifiée.
                    $instance = $instanceRepository->find($options->idInstance);
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


                    $gandiApi->paasUpdateDisk($connect, $instance->getIdGandi(), ($product->getFeatures()->part * $options->quantity));

                    // On met à jour la nouvelle taille.
                    $instance->setDataDiskAdditionalSize(($product->getFeatures()->part * $options->quantity));
                    $this->em->persist($instance);
                    break;
                // case produit appartenant à la catégorie "createinstance"
                case $product->getCategories()->contains($createInstanceCategory):

                    $options = json_decode($line->getOptions());
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

                    // $product contient : On loade le serveur pour connaitre le nbre de Go de base que l'instance devra avoir.
                    $optionsProducts = $product->getFeatures();
                    $hddDefault = $optionsProducts->tailleDisque;


                    $productRepository = $this->em->getRepository('AppBundle:Product');
                    // On loade la description du produit HDD pour connaitre le nbre de Go par tranche
                    //try {
                    $partHdd = $productRepository->findOneByReference('parthdd');
                    /* }catch(\Exception $e) {
                         throw new \SoapFault('e', $e->getMessage());
                     }*/

                    $nbreGoPerTranche = $partHdd->getFeatures()->part;

                    // Valeur du hdd par défaut, -10 = la part fixe de gandi + (le nbre de Go par tranche * le nbre de tranche)
                    $totalGoEnOptionsAAjouter = $hddDefault - 10 + ($nbreGoPerTranche * $options->partHdd);


                    $objPuissance = $productRepository->find($options->puissance);
                    $sizeServer = $objPuissance->getFeatures()->size;
                    $durationInMonths = $options->period;


                    $objNbreVhosts = $productRepository->find($options->nbreVhosts);
                    $dNbreVhosts = $this->em->getRepository('AppBundle:NumberVhostsInstance')->findOneByProduct($objNbreVhosts);

                    // Load du datacenter (Ici le 1 (Paris))
                    $datacenter = $this->em->getRepository('AppBundle:DataCenter')->find(1);

                    $idDatacenter = $datacenter->getId();
                    // Sauvegarde de l'instance chez Gandi

                    $idUserFinal = (property_exists($options, 'idUser') ? $options->idUser : $line->getUtilisateurPourLequelEstLeProduit());
                    $userFinal = $this->em->getRepository('AppBundle:User')->find($idUserFinal);


                    $name = date('ymdHis') . '_' . $userFinal->getCodeClient() . '_' . substr($userFinal->getName(), 0, 5);

                    $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);


                    $dSize = $this->em->getRepository('AppBundle:SizeInstance')->findOneByProduct($objPuissance);

                   // $dType = $this->em->getRepository('AppBundle:TypeInstance')->find(1);
                    $dType = $this->em->getRepository('AppBundle:TypeInstance')->findOneByName($options->language);
                    /**
                     * [{"errortype":null
                     * ,"date_updated":{"scalar":"20160218T16:26:41","timestamp":1455809201,"xmlrpc_type":"datetime"},
                     * "last_error":null,
                     * "date_start":null
                     * ,"session_id":20463311,
                     * "source":"GI47-GANDI",
                     * "step":"BILL","
                     * eta":312,
                     * "params":{"datacenter_id":1,"server_id":157013,"paas_id":157025,"tracker_id":"16cc7f3f-9db7-4628-9778-6e9630ab4d24","cancelable":"yes","trial":null,"duration":"1m","fixed":false}
                     * ,"date_created":{"scalar":"20160218T16:26:41","timestamp":1455809201,"xmlrpc_type":"datetime"},
                     * "infos":{"product_action":"create","product_type":"paas","id":"","extras":[],"label":"","product_name":"","quantity":""}
                     * ,"type":"paas_vm_create","id":51934784},
                     *
                     *
                     *
                     * {"errortype":null,
                     * "date_updated":{"scalar":"20160218T16:26:41","timestamp":1455809201,"xmlrpc_type":"datetime"},
                     * "last_error":null
                     * ,"date_start":null,
                     * "session_id":20463311,
                     * "source":"GI47-GANDI",
                     * "step":"BILL"
                     * ,"eta":270,
                     * "params":{"datacenter_id":1,"fixed":false,"resource_id":628010,"paas_id":157025,"tracker_id":"16cc7f3f-9db7-4628-9778-6e9630ab4d24","cancelable":"yes","duration":"1m","password":"0se65PaYlf","quantity":15}
                     * ,"date_created":{"scalar":"20160218T16:26:40","timestamp":1455809200,"xmlrpc_type":"datetime"},
                     * "infos":{"product_action":"create","product_type":"paas","id":"","extras":[],"label":"","product_name":"","quantity":"15"},
                     * "type":"paas_disk_create","id":51934781},
                     *
                     * {"errortype":null,"date_updated":{"scalar":"20160218T16:26:41","timestamp":1455809201,"xmlrpc_type":"datetime"}
                     * ,"last_error":null,
                     * "date_start":null
                     * ,"session_id":20463311,
                     * "source":"GI47-GANDI",
                     * "step":"BILL"
                     * ,"eta":282,
                     * "params":{"datacenter_id":1,"server_id":157013,"resource_id":628019,"paas_id":157025,"tracker_id":"16cc7f3f-9db7-4628-9778-6e9630ab4d24","cancelable":"yes","fixed":false}
                     * ,"date_created":{"scalar":"20160218T16:26:41","timestamp":1455809201,"xmlrpc_type":"datetime"}
                     * ,"infos":{"product_action":"rproxy_create","product_type":"paas","id":"","extras":[],"label":"","product_name":"","quantity":""},"type":"rproxy_create","id":51934787}
                     * ]
                     */
                    try {
                        $isMutu = false;
                        switch ($product->getReference()) {
                            case 'instance5.5':
                                $productRenew = $productRepository->findOneByReference('renewinstance5.5');
                                break;

                            case 'instance10':
                                $productRenew = $productRepository->findOneByReference('renewinstance10');
                                break;

                            case 'instance15':
                                $productRenew = $productRepository->findOneByReference('renewinstance15');
                                break;
                            case 'instanceimmo':
                                $productRenew = $productRepository->findOneByReference('renewinstanceimmo');
                                break;
                            case 'instanceimmoe':
                                $productRenew = $productRepository->findOneByReference('renewinstanceimmoe');
                                break;
                            case 'instance':
                                $productRenew = $productRepository->findOneByReference('renewinstance');
                                break;
                            case 'instancemutualisable':
                                $productRenew = $productRepository->findOneByReference('instancemutualisable');
                                $isMutu = true;
                                break;
                            /*    case '15s':
                                    $productRenew = $productRepository->findOneByReference('renewinstance15s');
                                    break;
                                case 'cloud':
                                    $productRenew = $productRepository->findOneByReference('renewinstancecloud');
                                    break;*/
                            default:
                                throw new \SoapFault('server', 'Type de produit inconnu');
                                break;

                        }


                        // $snapshotprofile = $this->em->getRepository('AppBundle:SnapshotProfileInstance')->find(1);
                        $snapshotprofile = $this->em->getRepository('AppBundle:SnapshotProfileInstance')->findOneByProduct($options->saveAuto);
                        // Si snapshot profile (id) = 2, sauvegardes désactivés. Donc on passe nul à la méthode Gandi.
                        if ($snapshotprofile->getId() == 2) {
                            $idGandiSnapshotProfile = null;
                        } else {
                            $idGandiSnapshotProfile = $snapshotprofile->getIdGandi();
                        }
                        //                    try {
                        $gandiInstance = $gandiApi->createInstance($connect, $idDatacenter, $durationInMonths, $name, $password, $dSize->getName(), $dType->getName(), $totalGoEnOptionsAAjouter, $idGandiSnapshotProfile);
                        /* }catch(\Exception $e) {
                             throw new \SoapFault('e', $totalGoEnOptionsAAjouter.'++'.$e->getMessage());
                         }*/

                    } catch (\Exception $e) {
                        throw new \SoapFault('e', $e->getMessage());
                    }
                    // Sauvegarde de l'instance chez nous
                    $newInstance = new Instance();
                    $newInstance->setProduct($product);
                    $newInstance->setProductRenew($productRenew);
                    $newInstance->setActive(true);
                    $newInstance->setName($name);
                    $newInstance->setUser($userFinal);
                    $newInstance->setDataCenter($datacenter);
                    $newInstance->setSizeInstance($dSize);
                    $newInstance->setTypeInstance($dType);
                    $newInstance->setNumberMaxVhosts($dNbreVhosts);

                    $newInstance->setIdGandi($gandiInstance[0]['id']);
                    $newInstance->setCatalogName('');
                    $newInstance->setConsole('');
                    $newInstance->setDataDiskAdditionalSize((int)0);
                    $newInstance->setDataDiskTotalSize('');
                    $newInstance->setFtpServer('');
                    $newInstance->setGitServer('');
                    $newInstance->setNeedUpgrade(false);
                    $newInstance->setUserFtp('');

                    // Profil par défaut
                    $newInstance->setSnapshopProfileInstance($snapshotprofile);
//                    $newInstance->setDateStart();
//                    $newInstance->setDateEnd();
//                    $newInstance->setDateEndCommitment();
                    $newInstance->setIsMutu($isMutu);
                    try {


                        $this->em->persist($newInstance);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        throw new \SoapFault('e', $e->getMessage());
                    }


                    // Envoi du mot de passe de l'instance à legrain.
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Mot de passe de l\'instance : ' . $newInstance->getName())
                        ->setFrom($this->email_app)
//                        ->setTo($userFinal->getEmail())
                        ->setTo('gweb@legrain.biz')
                        ->setBody(
                            $this->twig->render(
                            // app/Resources/views/Emails/registration.html.twig
                                'Email/password-instance.html.twig',
                                array('instance' => $newInstance, 'password' => $password)
                            ),
                            'text/html'
                        );
                    $this->mailer->send($message);


                    break;
                // case produit appartenant à la catégorie "puissanceInstance"
                case $product->getCategories()->contains($sizeInstanceCategory):

                    $options = json_decode($line->getOptions());
                    // Instance sur laquelle la puissance est modifiée.
                    $instance = $instanceRepository->find($options->idInstance);
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

                    $puissance = $sizeInstanceRepository->findOneByProduct($product->getId());
                    $gandiApi->paasUpdate($connect, $instance->getIdGandi(), $puissance->getName());

                    // On met à jour la nouvelle taille.
                    $instance->setSizeInstance($puissance);
                    $this->em->persist($instance);

                    break;
                // Produit nbre de vhosts
                case $product->getCategories()->contains($nbreVhostsInstanceCategory):

                    $options = json_decode($line->getOptions());
                    $instance = $instanceRepository->find($options->idInstance);


                    $numberVhostsCategoryRepository = $this->em->getRepository('AppBundle:NumberVhostsInstance');


                    $nbreVhosts = $numberVhostsCategoryRepository->findOneByProduct($product->getId());


                    // On met à jour le nouveau nombre de vhosts
                    $instance->setNumberMaxVhosts($nbreVhosts);
                    $this->em->persist($instance);

                    break;
                // case produit appartenant à la catégorie "renewndd"
                case $product->getCategories()->contains($renewNddCategory):
                    $options = json_decode($line->getOptions());
                    // On récupère le packpro
                    $ndd = $nddRepository->findOneByName($options->ndd);

                    $periodInMoth = $options->period;
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
                    $lastDate = $ndd->getExpirationDate();
                    $newDate = clone $lastDate->add(new \DateInterval('P' . ceil($options->period / 12) . 'Y'));

                    $log = new Log($userConnected, 'Renouvelement du domaine : ' . $ndd->getName() . ' pour ' . ($options->period / 12) . ' an(s)');

                    $this->em->persist($log);

                    $gandiApi->domainRenew($connect, $ndd->getName(), (int)ceil($options->period / 12));

                    // Met la bonne date (Mais pas encore à jour chez Gandi).
                    $ndd->setExpirationDate($newDate);

                    // if renew packMail
                    if ($options->packmail) {
                        $newLineRenew = new TmpRenewPackMail();
                        $newLineRenew->setNdd($ndd);
                        $newLineRenew->setPackMail($ndd->getEmailGandiPackPro());
                        $newLineRenew->setDateShouldBeTheDomain($newDate);
                        $this->em->persist($newLineRenew);

                    }
                    // Update date de fin ndd
                    $this->em->persist($ndd);
                    $this->em->flush();

                    break;

                // case produit appartenant à la catégorie "renewinstance"
                case $product->getCategories()->contains($renewInstanceCategory):
                    $options = json_decode($line->getOptions());
                    $instanceRepository = $this->em->getRepository('AppBundle:Instance');
                    // On récupère l'instance
                    $instance = $instanceRepository->findOneByName($options->instance);

                    // $periodInMonth = $options->period;
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
                    $lastDate = $instance->getDateEnd();
                    $newDate = clone $lastDate->add(new \DateInterval('P1Y'));

                    $log = new Log($userConnected, 'Renouvelement de l\'instance : ' . $instance->getName() . ' pour 1 an');

                    $this->em->persist($log);

                    $gandiApi->instanceRenew($connect, $instance->getIdGandi(), '12m');

                    // Met la bonne date (Mais pas encore à jour chez Gandi).
                    $instance->setDateEnd($newDate);

                    // Update date de fin ndd
                    $this->em->persist($instance);
                    $this->em->flush();

                    break;
                case $product->getCategories()->contains($productNddCreate):
                    $options = json_decode($line->getOptions());
                    $contactRepository = $this->em->getRepository('AppBundle:Contact');
                    $contact = $contactRepository->findOneByCode($options->contact);
                    $userProprioContact = $contact->getUser();

                    try {
                        // Ajout du ndd chez gandi :
                        $gandiApi = new \GandiBundle\Controller\GandiController();

                        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
                        // Quantité est en mois donc on le divide par 12 pour avoir l'année
                        $ownerContact = $contact->getCodeGandi();
                        $durationInYear = $line->getQuantity() / 12;

                        $adminContact = $this->identifiant_gandi_admin;
                        $billContact = $this->identifiant_gandi_bill;
                        $techContact = $this->identifiant_gandi_tech;

                        $gandiNewDomain = $gandiApi->domainCreate($connect, $options->ndd, $durationInYear, $ownerContact, $adminContact, $billContact, $techContact);

                        // Ajout du ndd dans l'application
                        $newNdd = new Ndd();
                        $newNdd->setContact($contact);
                        $newNdd->setUser($userProprioContact);
                        $newNdd->setName($options->ndd);
                        $newNdd->setProduct($product);
                        // Sera mis à jour au prochain passage de la méthode getNdd()
                        $newNdd->setIdGandi('445');
                        $in1Year = new \DateTime();
                        $in1Year->add(new \DateInterval('P' . $durationInYear . 'Y'));
                        $newNdd->setExpirationDate($in1Year);


                        $this->em->persist($newNdd);
                        $this->em->flush();
                    } catch (\SoapFault $e) {
                        throw new \SoapFault('e', $e->getMessage());
                    }

                    break;
                case $product->getCategories()->contains($instanceSauvegardeAutoCategory):
                    $options = json_decode($line->getOptions());


                    // Instance sur laquelle la sauvegarde est activée ou non.
                    $instance = $instanceRepository->find($options->idInstance);
                    $gandiApi = new \GandiBundle\Controller\GandiController();

                    $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
                    $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

                    $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

                    $snapshotProfile = $snapshotProfileInstanceRepository->findOneByProduct($product->getId());
                    // Profil "mort" (annulation de la sauvegarde, id =null)
                    if ($snapshotProfile->getId() == 2) {
                        // id profil snapshot pour désactiver l'option (null ne passe pas).
                        // en attente de la répose par gandi.
                        $idGandi = '';

                    } else {
                        $idGandi = $snapshotProfile->getIdGandi();
                    }
                    try {
                        $gandiApi->paasUpdateSnapshotProfile($connect, $instance->getIdGandi(), $idGandi);
                    } catch (\Exception $e) {
                        throw new \SoapFault('ee', $e->getMessage());
                    }

                    // On met à jour la nouvelle taille.
                    $instance->setSnapshopProfileInstance($snapshotProfile);
                    $this->em->persist($instance);

                    break;
                case $product->getCategories()->contains($produitHebergementCategory):
                    $this->traitementCartLineProduitHebergementMutualise($line, $userConnected, $userWhoPaid, $mouvement);
                    break;
            }
        }
        return $cart;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idline
     * @return bool
     * @throws \SoapFault
     */
    public function removeToCart($username, $password, $idline){

        $userConnected = $this->login($username, $password);

        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $cartLine = $cartLineRepository->find($idline);


        // Si la ligne (options) contiennent les infos lineInCart, on doit mettre cette ligne de nextPaement à true.
        $options = json_decode($cartLine->getOptions());
        if (property_exists($options, 'lineInCart')) {
            $nextPaiementRepository = $this->em->getRepository('AppBundle:NextPaiement');
            $np = $nextPaiementRepository->find($options->lineInCart);
            $np->setInCart(false);
            $this->em->persist($np);
        }
        // On regarde que le panier n'a pas été payé.
        if ($cartLine->getCart()->getIsPaid()) throw new \SoapFault('erreur', 'Impossible de supprimer une ligne déjà payée');
        $this->em->remove($cartLine);
        $this->em->flush();

        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idinstance
     * @param string $vhost
     * @return bool
     * @throws \SoapFault
     */
    public function toggleOptionMaintenance($username, $password, $idinstance, $vhost)
    {

        $userConnected = $this->login($username, $password);

        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $vhostRepository = $this->em->getRepository('AppBundle:Vhosts');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $instance = $instanceRepository->find($idinstance);
        if (!$userConnected->getRoles()->contains($roleLegrain)) throw new \SoapFault('forbidden', 'Accès interdit');

        $dvhost = $vhostRepository->findOneBy(array('instance' => $instance, 'name' => $vhost));
        if (!$dvhost) throw new \SoapFault('forbidden', 'Vhost introuvable');
        $etat = $dvhost->getInMaintenance();
        $etat = $etat ? false : true;

        $dvhost->setInMaintenance($etat);
        $this->em->persist($dvhost);
        $this->em->flush();
        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $jsonListProducts
     * @return bool
     * @throws \SoapFault
     */
    public function addListProductsToCart($username, $password, $jsonListProducts)
    {
        $list = json_decode($jsonListProducts);
        foreach ($list as $item) {
            //  throw new \SoapFault('e',$username.' '.$password.' '.$item->idProduct.' '.$item->iduser.' '.$item->options);
            $this->addToCart($username, $password, $item->idProduct, $item->iduser, $item->options);
        }

        return true;
    }

    private function calculPriceLine(Product $product,$options,User $userConnected,$username,$password,$iduserRequest,$libel=''){

        $productRepository = $this->em->getRepository('AppBundle:Product');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $emailGandiPackMailProRepository = $this->em->getRepository('AppBundle:EmailGandiPackPro');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $renewNddCategory = $productCategoryRepository->findOneByName('renewndd');
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $sizeInstanceRepository = $this->em->getRepository('AppBundle:SizeInstance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');

        // create ndd
        $createNddCategory = $productCategoryRepository->findOneByName('createndd');
        // On charge la catégorie renewInstances
        $renewInstanceCategory = $productCategoryRepository->findOneByName('renewinstance');
        $createInstanceCategory = $productCategoryRepository->findOneByName('instance');
        // On charge la catégorie "sizeInstance"
        $sizeInstanceCategory = $productCategoryRepository->findOneByName('puissanceInstance');
        // On charge la catégorie "optionPartHddInstance"
        $optionPartHddInstanceCategory = $productCategoryRepository->findOneByName('optionPartHddInstance');
        // Catégorie : Nombre_sites_maxi_par_instance
        $nbreVhostsInstanceCategory = $productCategoryRepository->findOneByName('Nombre_sites_maxi_par_instance');
        // Catégorie : instance_sauvegarde_auto
        $instanceSauvegardeAutoCategory = $productCategoryRepository->findOneByName('instance_sauvegarde_auto');
        // Catégorie : produit_generique_instance_mutualisable
        $produitGeneriqueInstanceMutualisableCategory = $productCategoryRepository->findOneByName('produit_generique_instance_mutualisable');
        // On charge la catégorie "optionInstance"
        $doctrineProduct = $productRepository->find($product->id);
        // On charge les produits qui ont cette catégory

        $return=array();
//        try {
        $userRequest = $userRepository->find($iduserRequest);
        $percentTax = $product->percentTax;
        if ($userRequest->getParent() != null) {
            $useTva = $userRequest->getAgency()->getUseTva();
            if (!$useTva) $percentTax = (int)0;
        }

        switch ($product->id) {
            case 1:
//                // On récupère les infos sur le ndd (date fin enregistrement).
                $labelNdd = $options->ndd;
                //$size = $options->size;
                $ndd = $nddRepository->findOneByName($labelNdd);
                // récupération du packMail
                $emailGandiPackMailPro = $emailGandiPackMailProRepository->findOneByNdd($ndd);
                if ($emailGandiPackMailPro) throw new \SoapFault('error', 'Vous possèdez déjà ce produit.');
                $dateFinDomaine = $ndd->getExpirationDate();
                // On ajoute le produit au panier.
                $options->size = 1;
                $quantity = (int)1;
                $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;// prixUnitaire
                $dateEnding = $dateFinDomaine->format('Y-m-d');// date fin domaine
                $prixAuProrata = $this->math->calculPrixAuProrata($dateEnding, $pricePerMonth, $quantity);
                $return['quantity']=$quantity;
                $return['totalHt']=$prixAuProrata;
                $return['percentTax']=($percentTax);
                $return['unitPrice']=($product->minPriceHT ? $product->minPriceHT : $product->priceHT);
                $return['totalTax'] =(round($prixAuProrata * $percentTax, 2));
                $return['libel']=$doctrineProduct->getName() . ' Pour le domaine : ' . $labelNdd . ' (abonnement jusqu\'au ' . $dateFinDomaine->format('d/m/Y') . ")";
                break;
            case 2:
                // Récup de certaine infos dans $product, et, calcul des prix au prorata. ( date ????)
                $labelNdd = $options->ndd;
                $ndd = $nddRepository->findOneByName($labelNdd);
                // récupération du packMail
                $emailGandiPackMailPro = $emailGandiPackMailProRepository->findOneByNdd($ndd);
                if (!$emailGandiPackMailPro) throw new \SoapFault('error', 'Vous pas déjà ce produit.');
                $dateFinDomaine = $ndd->getExpirationDate();
                $complement = 0;
                $complementUniqPrice = 0;
                $dateEnding = $dateFinDomaine->format('Y-m-d');// date fin domaine
                if ($options->size == 0) {
                    $return['libel']='Suppression du pack mail pour le domaine : ' . $labelNdd;
                    // récuperation du produit 1 (abonnement packMail)

                    $p11 = $this->getProduct($username, $password, 1, $iduserRequest);
                    $pricePerMonth = $p11->minPriceHT ? $p11->minPriceHT : $p11->priceHT;// prixUnitaire
                    $complement = -$this->math->calculPrixAuProrata($dateEnding, $pricePerMonth, 1);
                } else {
                    $return['libel']=$doctrineProduct->getName() . ' (' . ($options->size - $emailGandiPackMailPro->getSize() < 0 ? '' : '+') . ($options->size - $emailGandiPackMailPro->getSize()) . ' Go) Pour le domaine : ' . $labelNdd . ' (abonnement jusqu\'au ' . $dateFinDomaine->format('d/m/Y') . ')';
                }
                $quantity = $options->size - $emailGandiPackMailPro->getSize();
                $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;// prixUnitaire
                $prixAuProrata = $this->math->calculPrixAuProrata($dateEnding, $pricePerMonth, $quantity);
                $prixAuProrata += $complement;
                $return['quantity']=$quantity;
                $return['totalHt']=$prixAuProrata;
                $return['percentTax']=$percentTax;
                $return['unitPrice']=($product->minPriceHT ? $product->minPriceHT : $product->priceHT) + $complementUniqPrice;
                $return['totalTax']=round($prixAuProrata * $percentTax, 2);
                break;
            // case produit appartenant à la catégorie "instance"( create instance)
            case $doctrineProduct->getCategories()->contains($createInstanceCategory):
                $nbPartHdd = $options->partHdd;
                $period = $options->period;
                $idPuissance = $options->puissance;
                $idNbreVhosts = $options->nbreVhosts;
                $idSaveAuto = $options->saveAuto;
                // On loade le produit puissance :
                $pricePuissance = $this->getProduct($username, $password, $idPuissance, $iduserRequest);
                // On loade le produit partHdd
                $partHdd = $productRepository->findOneByReference('parthdd');
                $pricePartHdd = $this->getProduct($username, $password, $partHdd->getId(), $iduserRequest);
                // On loade le produit nbreVhosts
                $priceNbreVhosts = $this->getProduct($username, $password, $idNbreVhosts, $iduserRequest);
                // On loade le produit saveAuto
                $priceSaveAuto = $this->getProduct($username, $password, $idSaveAuto, $iduserRequest);
                // Calcul du début :
                // prix du serveur seul
                $pricePerMonth = ($product->minPriceHT ? $product->minPriceHT : $product->priceHT);// prixUnitaire
                //Ajout du prix de la puissance
                $pricePerMonth += ($pricePuissance->minPriceHT ? $pricePuissance->minPriceHT : $pricePuissance->priceHT);// prixUnitaire
                // Ajout du prix pour le nombre de site
                $pricePerMonth += ($priceNbreVhosts->minPriceHT ? $priceNbreVhosts->minPriceHT : $priceNbreVhosts->priceHT);// prixUnitaire
                $labelCart = 'Serveur : ' . $product->name . ' - ' . $pricePuissance->name . ' - ' . $priceNbreVhosts->name;
                // Si $nbPartHdd > 0
                if ($nbPartHdd > 0) {
                    $pricePerMonth += ($pricePartHdd->minPriceHT ? $pricePartHdd->minPriceHT : $pricePartHdd->priceHT) * $nbPartHdd;// prixUnitaire
                    $labelCart .= ' - Nombre part espace disque en option : ' . $nbPartHdd;
                }
                $labelCart .= ' - durée abonnement : ' . $period . ' mois';
                $test = ($priceSaveAuto->minPriceHT ? $priceSaveAuto->minPriceHT : $priceSaveAuto->priceHT);
                // Si different de 0
                if ($test != 0) {
                    //'DAns $product, il y a la taille disque par défaut'
                    // dans $partHdd il y a le nombre de Go par part
                    // $sizeDiskDefault = valeur par défaut sur le serveur de ce type
                    // $nbreGoEnOption =nbre de parts * nbre de Go par part
                    $nbreGoEnOption = $nbPartHdd * $partHdd->getFeatures()->part;
                    $sizeDiskDefault = $doctrineProduct->getFeatures()->tailleDisque;
                    //$sizeDisk
                    $nbreDeGoSave = .3 * ($sizeDiskDefault + $nbreGoEnOption);
                    $tmpPrice = ($nbreDeGoSave * ($priceSaveAuto->minPriceHT ? $priceSaveAuto->minPriceHT : $priceSaveAuto->priceHT));
                    $pricePerMonth += $tmpPrice;// prixUnitaire
                    $labelCart .= ' - sauvegarde auto : (' . $nbreDeGoSave . 'Go soit ' . $tmpPrice . '€/mois)';
                }
                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=($period);
                $pricePeriod = round(($pricePerMonth) * $period, 2);
                $return['totalHt']=(round($pricePeriod, 2));
                $return['percentTax']= ($percentTax);
                $return['unitPrice']=(round($pricePerMonth, 2));
                $return['totalTax']=(round($pricePeriod * $percentTax, 2));
                $return['libel']=$labelCart;
                break;
            // case produit appartenant à la catégorie "createndd"
            case $doctrineProduct->getCategories()->contains($createNddCategory):
                $labelNdd = $options->ndd;
                $period = $options->period;
                if (!property_exists($options, 'contact')) $options->contact = null;
                $return['libel'] = $doctrineProduct->getName() . ' (+' . ($options->period / 12) . ' an(s)) Pour le domaine : ' . $labelNdd . ') ';
                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=$period;
                $pricePerMonth = ($product->minPriceHT ? $product->minPriceHT : $product->priceHT);// prixUnitaire
                $pricePeriod = round(($pricePerMonth) * $period, 2);
                $return['totalHt']=(round($pricePeriod, 2));
                $return['percentTax']=($percentTax);
                $return['unitPrice']=(round($product->minPriceHT ? $product->minPriceHT : $product->priceHT, 2));
                $return['totalTax']=(round($pricePeriod * $percentTax, 2));

                break;
            // case produit appartenant à la catégorie "renewndd"
            case $doctrineProduct->getCategories()->contains($renewNddCategory):
                // si la ligne existe pour ce domaine, on la met à jour.
                // On récupère les infos sur le ndd (date fin enregistrement).
                $labelNdd = $options->ndd;
                $period = $options->period;
                // On récupère le panier du tiers.
                $ndd = $nddRepository->findOneByName($labelNdd);
                $dateFinDomaine = $ndd->getExpirationDate();
                $complementLabel = '';
                $prixAuProrata = 0;
                // On regarde si le ndd a un pack mail. Si oui, on l'ajoute au panier (et calcul du prix)
                $packMail = $ndd->getEmailGandiPackPro();
                $options->packmail = false;
                if ($packMail) {
                    $productPackMail = $this->getProduct($username, $password, 1, $iduserRequest);
                    $productPackMail1 = $this->getProduct($username, $password, 2, $iduserRequest);
                    $complementLabel = '+ pack mail (' . $packMail->getSize() . 'Go)';
                    // Récup de certaine infos dans $product, et, calcul des prix au prorata. ( date ????)
                    // Abonnement
                    if ($packMail->getSize() == 1) {
                        $pricePerMonth = $productPackMail1->minPriceHT ? $productPackMail1->minPriceHT : $productPackMail1->priceHT;// prixUnitaire
                        $dateBegin = date('Y-m-d', strtotime($dateFinDomaine->format('Y-m-d') . '+' . $options->period . ' months'));// date fin domaine
                        $dateEnding = $dateFinDomaine->format('Y-m-d');// date fin domaine
                        $quantity = 1;// Toujours 1 pour l'abonnement
                        $prixAuProrata = $this->math->calculPrixAuProrata($dateEnding, $pricePerMonth, $quantity, $dateBegin);
                    }
                    // Go
                    if ($packMail->getSize() > 1) {
                        $pricePerMonth2 = $productPackMail->minPriceHT ? $productPackMail->minPriceHT : $productPackMail->priceHT;// prixUnitaire
                        // $newTimestamp = strtotime('+2 years', $timestamp);
                        //date('d/m/Y',strtotime('2015-11-11 +2 year'));
                        $dateBegin = date('Y-m-d', strtotime($dateFinDomaine->format('Y-m-d') . '+' . $options->period . ' months'));// date fin domaine
                        $dateEnding = $dateFinDomaine->format('Y-m-d');// date fin domaine
                        $quantity = $packMail->getSize();// nbre de Go dans le pack actuel.
                        $prixAuProrata += $this->math->calculPrixAuProrata($dateEnding, $pricePerMonth2, $quantity, $dateBegin);
                    }
                    $options->packmail = true;
                }
                // On ajoute le produit au panier.
                $return['libel']=$doctrineProduct->getName() . ' ' . $complementLabel . ' (+' . ($options->period / 12) . ' an(s)) Pour le domaine : ' . $labelNdd . ') ';
                $return['quantity']=($period);
                $pricePerMonth = ($product->minPriceHT ? $product->minPriceHT : $product->priceHT);// prixUnitaire
                $pricePeriod = round(($pricePerMonth) * $period + $prixAuProrata, 2);
                $return['totalHt']=(round($pricePeriod, 2));
                $return['percentTax']=($percentTax);
                $return['unitPrice']=(round($product->minPriceHT ? $product->minPriceHT : $product->priceHT, 2));
                $return['totalTax']=(round($pricePeriod * $percentTax, 2));
                break;
            // case produit appartenant à la catégorie "puissanceInstance"
            case $doctrineProduct->getCategories()->contains($sizeInstanceCategory):

                // idInstance dans options
                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');


                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
//                if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');



// Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');                    }
                }


                $dateFin = $instance->getDateEnd();
                $dateFin = $dateFin->format('Y-m-d');
                $ancienProduit = $instance->getSizeInstance()->getProduct();
                $nouveauProduit = $productRepository->find($product->id);

                $objAncienProduit = $this->getProduct($username, $password, $ancienProduit->getId(), $iduserRequest);
                $prixAncienProduit = $objAncienProduit->minPriceHT ? $objAncienProduit->minPriceHT : $objAncienProduit->priceHT;

                $objNouveauProduit = $this->getProduct($username, $password, $nouveauProduit->getId(), $iduserRequest);
                $prixNouveauProduit = $objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT;
                // Calcul des proratas,

                // récupèrer l'option en cours et calculer le montant à rembourser
                $prixAuProrataAncienProduit = $this->math->calculPrixAuProrata($dateFin, $prixAncienProduit, 1);
                // Calculer le prix au proratat du temps restant de la nouvelle option
                $prixAuProrataNouveauProduit = $this->math->calculPrixAuProrata($dateFin, $prixNouveauProduit, 1);
                // prix total :
                $prixHtTotal = $prixAuProrataNouveauProduit - $prixAuProrataAncienProduit;




//                $return['nouveauProduit']=($nouveauProduit);
                $return['libel']=$nouveauProduit->getName() . '  (pour le serveur : ' . $instance->getName() . ' ) ';
                // Récup de certaine infos dans $product, et, calcul des prix au prorata. ( date ????)

                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=(1);

                $return['totalHt']=(round($prixHtTotal, 2));
                $return['percentTax']=($percentTax);
                $return['unitPrice']=(round($objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT, 2));
                $return['totalTax']=(round($prixHtTotal * $percentTax, 2));

                break;
            // case produit appartenant à la catégorie "optionPartHddInstance"
            case $doctrineProduct->getCategories()->contains($optionPartHddInstanceCategory):
                $instanceRepository = $this->em->getRepository('AppBundle:Instance');

                // idInstance dans options
                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');


                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
               // if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');

// Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');                    }
                }

                $dateFin = $instance->getDateEnd();
                $dateFin = $dateFin->format('Y-m-d');

                $objProduitPartHdd = $this->getProduct($username, $password, $doctrineProduct->getId(), $iduserRequest);
                $prixProduitPartHddPerMonth = $objProduitPartHdd->minPriceHT ? $objProduitPartHdd->minPriceHT : $objProduitPartHdd->priceHT;
                // On calcule le tarif restant pour 1 pack pour le temps restant
                // Calcul des proratas,

                // récupèrer l'option en cours et calculer le montant à rembourser
                $InstanceFeatures = $instance->getProduct()->getFeatures();
                $gandiTotalDataDiskSize = $instance->getDataDiskAdditionalSize() + 10;
                $nbPartAncienne = ($gandiTotalDataDiskSize - $InstanceFeatures->tailleDisque) / $doctrineProduct->getFeatures()->part;


                // Calcul du prix pour le nouveau nombre de part ( nouveau nombre - ancien)
                $prixAuProrataProduit = $this->math->calculPrixAuProrata($dateFin, $prixProduitPartHddPerMonth, $options->quantity - $nbPartAncienne);


                $return['libel']=$doctrineProduct->getName() . '  (pour le serveur : ' . $instance->getName() . ' (+' . (($options->quantity - $nbPartAncienne) * $doctrineProduct->getFeatures()->part) . 'Go)) ';
                // Récup de certaine infos dans $product, et, calcul des prix au prorata. ( date ????)



                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=(1);

                $return['totalHt']=(round($prixAuProrataProduit, 2));
                $return['percentTax']=($percentTax);
                $return['unitPrice']=(round($objProduitPartHdd->minPriceHT ? $objProduitPartHdd->minPriceHT : $objProduitPartHdd->priceHT, 2));
                $return['totalTax']=(round($prixAuProrataProduit * $percentTax, 2));
                break;
            // case produit appartenant à la catégorie "renewinstance"
            case $doctrineProduct->getCategories()->contains($renewInstanceCategory):
                // si la ligne existe pour cette instance, on la met à jour.
                // On récupère les infos sur le ndd (date fin enregistrement).
                $labelInstance = $options->instance;
                $period = $options->period;
                $instanceRepository = $this->em->getRepository('AppBundle:Instance');
                $instance = $instanceRepository->findOneByName($labelInstance);
                // On regarde s'il est déjà dans le panier pour ce tiers et CE NDD ( chercher dans les options). ( si c'est le cas, on met à jr)
                // On récupere les lignes pour ce produit et ce panier

                $dateFinInstance = $instance->getDateEnd();

                // On calcule le prix de l'instance hors options
                $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;// prixUnitaire
                $productName = $doctrineProduct->getName();
                // On calcule le prix des options.

                // Option 1 : puissance
                $productPuissance = $instance->getSizeInstance()->getProduct();
                $objProductPuissance = $this->getProduct($username, $password, $productPuissance->getId(), $iduserRequest);
                $productName .= ' - ' . $productPuissance->getName();
                $pricePerMonth += $objProductPuissance->minPriceHT ? $objProductPuissance->minPriceHT : $objProductPuissance->priceHT;// prixUnitaire

                // Option 2 : part hébergement en option. (if)
                // On compte le nombre de part d'hébergement que possede l'instance (calcul)

                $totalHddGandi = $instance->getDataDiskAdditionalSize() + 10;
                $productHdd = $instance->getProductPartHdd();
                $tailleDisqueBase = $instance->getProductRenew()->getFeatures()->tailleDisque;
                $partInGo = $productHdd->getFeatures()->part;

                $dataAdditional = $totalHddGandi - $tailleDisqueBase;
                $nbPart = ceil($dataAdditional / $partInGo);

                if ($nbPart > 0) {
                    $objProductHdd = $this->getProduct($username, $password, $productHdd->getId(), $iduserRequest);
                    // On calcule les prix par mois * par le nombre de part
                    $productName .= ' - Part hdd en options : ' . $nbPart;
                    $pricePerMonth += ($objProductHdd->minPriceHT ? $objProductHdd->minPriceHT : $objProductHdd->priceHT) * $nbPart;// prixUnitaire
                }

                // Option 3 (maintenances sur les vhosts
                // On compte le nombre de vhosts en maintenance.

                $nbVhosts = 0;
                foreach ($instance->getVhosts() as $vhost) {
                    if ($vhost->getInMaintenance()) $nbVhosts++;
                }
                if ($nbVhosts > 0) {
                    // On charge le produit dont la reference est simplehostingmaintenance
                    $dMaintenance = $productRepository->findOneByReference('simplehostingmaintenance');
                    $maint = $this->getProduct($username, $password, $dMaintenance->getId(), $instance->getUser()->getId());
                    $priceMainteance = $maint->minPriceHT ? $maint->minPriceHT : $maint->priceHT;
                    //$options[]="Site(s) en maintenance technique (".$priceMainteance."€ HT /mois et par site sous maintenance) : ".$nbVhosts;

                    $productName .= ' - ' . $dMaintenance->getName() . ' (' . $nbVhosts . ' site(s))';
                    $pricePerMonth += $priceMainteance * $nbVhosts;// prixUnitaire
                }

                // Option 4 : nombre de vhosts
                $productNbreVhosts = $instance->getNumberMaxVhosts()->getProduct();
                $objProductNbreVhosts = $this->getProduct($username, $password, $productNbreVhosts->getId(), $iduserRequest);
                $productName .= ' - ' . $productNbreVhosts->getName();
                $pricePerMonth += $objProductNbreVhosts->minPriceHT ? $objProductNbreVhosts->minPriceHT : $objProductNbreVhosts->priceHT;// prixUnitaire

                // Option 5 : Sauvegarde automatique :
                $productProfilSauvegarde = $instance->getSnapshopProfileInstance();
                // 2 = pas de sauvegarde auto
                if ($productProfilSauvegarde->getId() != 2) {

                    $objProductSauvegardeAuto = $this->getProduct($username, $password, $productProfilSauvegarde->getProduct()->getId(), $iduserRequest);
                    $productName .= ' - ' . $productProfilSauvegarde->getProduct()->getName();
                    $pricePerMonth += (.3 * (10 + (int)$instance->getDataDiskAdditionalSize()) * ($objProductSauvegardeAuto->minPriceHT ? $objProductSauvegardeAuto->minPriceHT : $objProductSauvegardeAuto->priceHT));// prixUnitaire
                }

                $return['libel']=$productName . ' (+' . ($options->period / 12) . ' an(s)) Pour l\'instance : ' . $labelInstance . ' )';
                // Récup de certaine infos dans $product, et, calcul des prix au prorata. ( date ????)
                $pricePeriod = round($pricePerMonth * $period, 2);
                $return['quantity']=($period);
                $return['totalHt']=(round($pricePeriod, 2));
                $return['percentTax']=($percentTax);
                $return['unitPrice']=(round($pricePerMonth, 2));
                $return['totalTax']=(round($pricePeriod * $percentTax, 2));
                break;
            case $doctrineProduct->getCategories()->contains($nbreVhostsInstanceCategory):
                $instanceRepository = $this->em->getRepository('AppBundle:Instance');

                // idInstance dans options
                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');


                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
               // if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');
// Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');                    }
                }

                $dateFin = $instance->getDateEnd();
                $dateFin = $dateFin->format('Y-m-d');
                $ancienProduit = $instance->getNumberMaxVhosts()->getProduct();
                $nouveauProduit = $doctrineProduct;

                $objAncienProduit = $this->getProduct($username, $password, $ancienProduit->getId(), $iduserRequest);
                $prixAncienProduit = $objAncienProduit->minPriceHT ? $objAncienProduit->minPriceHT : $objAncienProduit->priceHT;
                $objNouveauProduit = $this->getProduct($username, $password, $nouveauProduit->getId(), $iduserRequest);
                $prixNouveauProduit = $objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT;
                // Calcul des proratas,
                // récupèrer l'option en cours et calculer le montant à rembourser
                $prixAuProrataAncienProduit = $this->math->calculPrixAuProrata($dateFin, $prixAncienProduit, 1);
                // Calculer le prix au proratat du temps restant de la nouvelle option
                $prixAuProrataNouveauProduit = $this->math->calculPrixAuProrata($dateFin, $prixNouveauProduit, 1);
                // prix total :
                $prixHtTotal = $prixAuProrataNouveauProduit - $prixAuProrataAncienProduit;
                $return['libel']=$nouveauProduit->getName() . '  (pour le serveur : ' . $instance->getName() . ' ) ';
                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=(1);
                $return['totalHt']=round($prixHtTotal, 2);
                $return['percentTax']=($percentTax);
                $return['unitPrice']=round($objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT, 2);
                $return['totalTax']=round($prixHtTotal * $percentTax, 2);
                break;

            case $doctrineProduct->getCategories()->contains($instanceSauvegardeAutoCategory):
                $instanceRepository = $this->em->getRepository('AppBundle:Instance');
                // idInstance dans options
                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');
                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
//                if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');
// Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduserRequest) throw new \SoapFault('error', 'impossible de modifier cette instance');                    }
                }
                $dateFin = $instance->getDateEnd();
                $dateFin = $dateFin->format('Y-m-d');
                $tailleDisqueMaxi = 10 + $instance->getDataDiskAdditionalSize();
                $ancienProduit = $instance->getSnapshopProfileInstance()->getProduct();
                $nouveauProduit = $doctrineProduct;

                $objAncienProduit = $this->getProduct($username, $password, $ancienProduit->getId(), $iduserRequest);
                // 30% de la taille maxi
                $prixAncienProduit = $tailleDisqueMaxi * .3 * ($objAncienProduit->minPriceHT ? $objAncienProduit->minPriceHT : $objAncienProduit->priceHT);

                $objNouveauProduit = $this->getProduct($username, $password, $nouveauProduit->getId(), $iduserRequest);
                // 30% de la taille maxi
                $prixNouveauProduit = $tailleDisqueMaxi * .3 * ($objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT);
                // Calcul des proratas,

                // récupèrer l'option en cours et calculer le montant à rembourser
                $prixAuProrataAncienProduit = $this->math->calculPrixAuProrata($dateFin, $prixAncienProduit, 1);
                // Calculer le prix au proratat du temps restant de la nouvelle option
                $prixAuProrataNouveauProduit = $this->math->calculPrixAuProrata($dateFin, $prixNouveauProduit, 1);
                // prix total :
                $prixHtTotal = $prixAuProrataNouveauProduit - $prixAuProrataAncienProduit;




                $return['libel']=$nouveauProduit->getName() . '  (pour le serveur : ' . $instance->getName() . ' ) ';
                // $optionsSize - cequiestdéjà associé au client
                $return['quantity']=(1);
                $return['totalHt']=round($prixHtTotal, 2);
                $return['percentTax']=($percentTax);
                $return['unitPrice']=round($objNouveauProduit->minPriceHT ? $objNouveauProduit->minPriceHT : $objNouveauProduit->priceHT, 2);
                $return['totalTax']=round($prixHtTotal * $percentTax, 2);
                break;
            case $doctrineProduct->getCategories()->contains($produitGeneriqueInstanceMutualisableCategory):
                $complementLabel = $libel;
                // On loade le produit heber

                $productHosting = $productHostingRepository->find($options->idProduitHeber);

                if( ($userRequest->getParent()==null && $productHosting->getAgency()->getId() == $userRequest->getAgency()->getId()) || $userRequest->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN')) ){
                    $prixHtTotal = (int)0;
                    $prixUnitHt = (int)0;
                }else{
                    $prixHtTotal = $options->period * $productHosting->getPriceHt();
                    $prixUnitHt = $productHosting->getPriceHt();
                }
                $return['libel']=$productHosting->getName() . $complementLabel . ' (' . $options->period . ' mois)';
                $return['quantity']=$options->period;
                $return['totalHt']=round($prixHtTotal, 2);
                $return['percentTax']=($percentTax);
                $return['unitPrice']=round($prixUnitHt, 2);
                $return['totalTax']=round($prixHtTotal * $percentTax, 2);
                break;
        }

        return json_encode($return);
    }
    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @param int $iduser
     * @param string $options
     * @return bool
     * @throws \SoapFault
     */
    public function addToCart($username, $password, $idProduct, $iduser, $options){
        // init
        $userRepository = $this->em->getRepository('AppBundle:User');
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $nextPaiementRepository = $this->em->getRepository('AppBundle:NextPaiement');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');

        $instanceSauvegardeAutoCategory = $productCategoryRepository->findOneByName('instance_sauvegarde_auto');

        $nbreVhostsInstanceCategory = $productCategoryRepository->findOneByName('Nombre_sites_maxi_par_instance');




        $options = json_decode($options);
        $userConnected = $this->login($username, $password);

        // $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        // $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        $agencylegrain = $agencyRepository->find(1);
        $userRequest = $userRepository->find($iduser);
        if ($userRequest->getRoles()->contains($roleCompteEmail)) throw new \SoapFault('FORBIDDEN', 'Accès interdit, ou, l\'utilisateur ne peut pas avoir de panier');

        // récuperation du panier, ou création s'il n'existe pas encore
        $cart = $cartRepository->findOneby(array('user' => $userConnected, 'isPaid' => 0));
        if ($cart == null) {
            $cart = new Cart();
            $cart->setUser($userConnected);
            $this->em->persist($cart);
            $this->em->flush();

        }

        if (property_exists($options, 'lineInCart')) {
            $np = $nextPaiementRepository->find($options->lineInCart);
            $np->setInCart(true);
            $this->em->persist($np);
        }
        $product = $this->getProduct($username, $password, $idProduct, $iduser);
        $doctrineProduct = $productRepository->find($product->id);


        $sizeInstanceCategory = $productCategoryRepository->findOneByName('puissanceInstance');
        $optionPartHddInstanceCategory = $productCategoryRepository->findOneByName('optionPartHddInstance');
        $produitGeneriqueInstanceMutualisableCategory = $productCategoryRepository->findOneByName('produit_generique_instance_mutualisable');

        // Verif des lignes du panier s'il y a besoin
        $lineExist = false;
        $cartLine = new CartLine();
        $libel='';

        switch ($idProduct) {
            case 1:
            case 2:
                $labelNdd = $options->ndd;
                // On regarde s'il est déjà dans le panier pour ce tiers et CE NDD ( chercher dans les options). ( si c'est le cas, on met à jr)
                // On récupere les lignes pour ce produit et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct));
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {
                        if (!$lineExist) {
                            $tmpOptions = json_decode($l->getOptions());
                            if ($tmpOptions->ndd == $labelNdd) {
                                $lineExist = true;
                                $cartLine = $l;
                            }
                        }
                    }
                }

                break;
            case $doctrineProduct->getCategories()->contains($productCategoryRepository->findOneByName('createndd')):
                $labelNdd = $options->ndd;
                // On regarde s'il est déjà dans le panier pour ce tiers et CE NDD ( chercher dans les options). ( si c'est le cas, on met à jr)
                // On récupere les lignes pour ce produit et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct));
                $lineExist = false;
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {
                        if (!$lineExist) {
                            $tmpOptions = json_decode($l->getOptions());
                            if ($tmpOptions->ndd == $labelNdd) {
                                $this->em->remove($l);
                            }
                        }
                    }
                    $this->em->flush();
                }
                break;
            case $doctrineProduct->getCategories()->contains($productCategoryRepository->findOneByName('renewndd')):
                $labelNdd = $options->ndd;
                // On regarde s'il est déjà dans le panier pour ce tiers et CE NDD ( chercher dans les options). ( si c'est le cas, on met à jr)
                $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                // On récupere les lignes pour ce produit et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct));
                $lineExist = false;
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {
                        if (!$lineExist) {
                            $tmpOptions = json_decode($l->getOptions());
                            if ($tmpOptions->ndd == $labelNdd) {
                                $this->em->remove($l);
                            }
                        }


                    }
                    $this->em->flush();
                }
                break;
            case $doctrineProduct->getCategories()->contains($sizeInstanceCategory):

                $idInstance = $options->idInstance;
                $idProductsSizeCategory = array();
                foreach ($sizeInstanceCategory->getProducts() as $p) {
                    $idProductsSizeCategory[] = $p->getId();
                }
                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
//                if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance');
                // Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance ');
                    }
                }

                // On récupere les lignes pour un des produits de la categorie "sizeInstanceCategory et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $idProductsSizeCategory));
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {

                        $tmpOptions = json_decode($l->getOptions());
                        if ($tmpOptions->idInstance == $instance->getId()) {
                            // S'il y est, on supprime la ligne
                            $this->em->remove($l);
                        }
                    }
                    $this->em->flush();
                }


                break;
            case $doctrineProduct->getCategories()->contains($optionPartHddInstanceCategory):
                $idProductsSizeCategory = array();
                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');
                foreach ($optionPartHddInstanceCategory->getProducts() as $p) {
                    $idProductsSizeCategory[] = $p->getId();
                }
                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance ');
                    }
                }

                // On regarde s'il est déjà dans le panier pour ce tiers pour ce serveur
                $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                // On récupere les lignes pour un des produits de la categorie "sizeInstanceCategory et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct->getId()));
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {

                        $tmpOptions = json_decode($l->getOptions());
                        if ($tmpOptions->idInstance == $instance->getId()) {
                            // S'il y est, on supprime la ligne
                            $this->em->remove($l);
                        }
                    }
                    $this->em->flush();
                }

                break;
            case $doctrineProduct->getCategories()->contains($productCategoryRepository->findOneByName('renewinstance')):
                // si la ligne existe pour cette instance, on la met à jour.
                // On récupère les infos sur le ndd (date fin enregistrement).
                $labelInstance = $options->instance;
                $period = $options->period;
                $instanceRepository = $this->em->getRepository('AppBundle:Instance');

                $instance = $instanceRepository->findOneByName($labelInstance);
                // On regarde s'il est déjà dans le panier pour ce tiers et CE NDD ( chercher dans les options). ( si c'est le cas, on met à jr)
                $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                // On récupere les lignes pour ce produit et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct));
                $lineExist = false;
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {
                        if (!$lineExist) {
                            $tmpOptions = json_decode($l->getOptions());
                            if ($tmpOptions->instance == $labelInstance) {

                                $this->em->remove($l);

                            }
                        }
                    }
                    $this->em->flush();
                }

                break;
            case $doctrineProduct->getCategories()->contains($nbreVhostsInstanceCategory):

                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');

                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');
                // Le serveur appartient il à iduser
//                if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance');
// Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance ');
                    }
                }
                $idProductsNbreVhostsCategory = array();
                foreach ($nbreVhostsInstanceCategory->getProducts() as $p) {
                    $idProductsNbreVhostsCategory[] = $p->getId();
                }

                // On regarde s'il est déjà dans le panier pour ce tiers pour ce serveur
                $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                // On récupere les lignes pour un des produits de la categorie "sizeInstanceCategory et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $idProductsNbreVhostsCategory));
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {

                        $tmpOptions = json_decode($l->getOptions());
                        if ($tmpOptions->idInstance == $instance->getId()) {
                            // S'il y est, on supprime la ligne
                            $this->em->remove($l);
                        }
                    }
                    $this->em->flush();
                }

                break;
            case $doctrineProduct->getCategories()->contains($instanceSauvegardeAutoCategory):

                $idInstance = $options->idInstance;
                if (!$idInstance) throw new \SoapFault('error', 'Vous devez définir un id instance');

                $instance = $instanceRepository->find($idInstance);
                if ($instance == null) throw new \SoapFault('error', 'Cette instance n\'existe pas');

                // Le serveur appartient il à iduser ou legrain, ou, gestionnaire de l'utilisateur
                if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {

                    if(!$userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE')&&$instance->getUser()->getId()!=$userConnected->getId())) {
                        if ($instance->getUser()->getId() != $iduser) throw new \SoapFault('error', 'impossible de modifier cette instance ');
                    }
                }

                $idProductsCategory = array();
                foreach ($instanceSauvegardeAutoCategory->getProducts() as $p) {
                    $idProductsCategory[] = $p->getId();
                }

                // On regarde s'il est déjà dans le panier pour ce tiers pour ce serveur
                $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                // On récupere les lignes pour un des produits de la categorie "sizeInstanceCategory et ce panier
                $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $idProductsCategory));
                if ($testCartLines) {
                    foreach ($testCartLines as $l) {

                        $tmpOptions = json_decode($l->getOptions());
                        if ($tmpOptions->idInstance == $instance->getId()) {
                            // S'il y est, on supprime la ligne
                            $this->em->remove($l);
                        }
                    }
                    $this->em->flush();
                }

                break;
            case $doctrineProduct->getCategories()->contains($produitGeneriqueInstanceMutualisableCategory):

//                $this->addHebergementMutualiseToCart($options, $iduser, $userConnected, $produitGeneriqueInstanceMutualisableCategory, $doctrineProduct, $username, $password, $userRequest);

                $libel = '';
                // On regarde si le produit est déjà présent pour ce hosting (renouvellement)
                if (property_exists($options, 'idHosting')) {

                    $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
                    // On récupere les lignes pour ce produit et ce panier
                    $testCartLines = $cartLineRepository->findBy(array('cart' => $cart, 'product' => $doctrineProduct));
                    $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
                    $hosting = $hostingRepository->find($options->idHosting);
                    if ($hosting) {
                        if ($hosting->getVhost() != null) {
                            $libel = ' : ' . $hosting->getVhost();
                        }
                    }

                    $lineExist = false;
                    if ($testCartLines) {
                        foreach ($testCartLines as $l) {
                            if (!$lineExist) {
                                $tmpOptions = json_decode($l->getOptions());
                                if (property_exists($tmpOptions, 'idHosting')) {
                                    if ($tmpOptions->idHosting == $options->idHosting) {
                                        $this->em->remove($l);
                                    }
                                }
                            }

                        }
                        $this->em->flush();
                    }

                }
                break;
        }

        if (!$lineExist) {
            $cartLine = new CartLine();
        }

        $res = json_decode($this->calculPriceLine($product,$options,$userConnected,$username,$password,$iduser,$libel));

        if(property_exists($res, 'nouveauProduit') ){
            $cartLine->setProduct($res->nouveauProduit);

            $cartLine->setProductReference($res->nouveauProduit->getReference());
        }else{
            $cartLine->setProduct($doctrineProduct);
            $cartLine->setProductReference($doctrineProduct->getReference());

        }

        $cartLine->setQuantity($res->quantity);
        $cartLine->setTotalHt($res->totalHt);
        $cartLine->setPercentTax($res->percentTax);
        $cartLine->setUnitPrice($res->unitPrice);
        $cartLine->setTotalTax($res->totalTax);
        $cartLine->setUtilisateurPourLequelEstLeProduit($userRequest);
        $cartLine->setProductName($res->libel);
        $cartLine->setOptions(json_encode($options));
        $cart->addCartLine($cartLine);
        $this->em->persist($cartLine);
        $this->em->flush();
        return true;


    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\Category[]
     * @throws \SoapFault
     */
    public function listProductsCategories($username, $password)
    {
        $userConnected = $this->login($username, $password);
        // $userAction = $userRepository->find($iduser);
        $categoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $categories = array();
        foreach ($categoryRepository->findAll() as $c) {
            $categories[] = new Category($c->getId(), $c->getName(), $c->getIsVisible());
        }
        return $categories;
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idPriceList
     * @return \AppBundle\Soap\Entity\PriceListLine[]
     * @throws \SoapFault
     */
    public function listPriceListLines($username, $password, $idPriceList)
    {
        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
        $priceList = $priceListRepository->find($idPriceList);
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $products = $productRepository->findBy(array('active' => true));
        $return = array();
        foreach ($products as $product) {
            $line = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceList));
            $price = $line == null ? null : $line->getPrice();
            $minPrice = $line == null ? null : $line->getMinPrice();
            $id = $line == null ? null : $line->getId();
            $return[] = new \AppBundle\Soap\Entity\PriceListLine($id, $product->getId(), $product->getName(), $price, $minPrice);

        }
        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idPriceList
     * @param string $jsonOptions
     * @return bool
     * @throws \SoapFault
     */
    public function updateListPriceListLines($username, $password, $idPriceList, $jsonOptions)
    {
        $allowed = false;

        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $tvaRepository = $this->em->getRepository('AppBundle:TvaRate');
        // tva à 20%
        $tva = $tvaRepository->find(1);

        $priceList = $priceListRepository->find($idPriceList);
        // Il faut regarder si la liste de prix à modifier appartient à l'agence et que l'utilsiateur connecté en est le gestionnaire.
        // Ou, que l'utilisateur connecté est Legrain (role : ROLE_LEGRAIN)
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($userConnected->getRoles()->contains($roleLegrain)) $allowed = true;
        elseif ($userConnected->getRoles()->contains($roleGestionnaire) && $priceList->getParentAgency()->getId() == $agency->getId()) $allowed = true;
        if (!$allowed) throw new \SoapFault('serveur', 'Accès refusé');

        $options = json_decode($jsonOptions);
        foreach ($options as $item) {
            // On vérifie que toutes les proprieté existent
            if (!property_exists($item, 'idProduct')) throw new \SoapFault('serveur', 'options doit contenir un champ idProduct');
            if (!property_exists($item, 'price')) throw new \SoapFault('serveur', 'options doit contenir un champ price');
            if (!property_exists($item, 'minPrice')) throw new \SoapFault('serveur', 'options doit contenir un champ minPrice');
            $product = $productRepository->find($item->idProduct);
            if ($product == null) throw new \SoapFault('serveur', 'Le produit dont l\'id est :' . $item->idProduct . ' n\'existe pas');
            // On update les lignes de la liste, on la créé si elle n'existe pas et, on la supprime si elle existait mais plus maintenant
            $line = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceList));

            if ($line == null) {
                $line = new PriceListLine();
                $line->setProduct($product);
                $line->setPriceList($priceList);
                $line->setTvaRate($tva);

            }

            if ($item->price == null && $item->minPrice == null) $this->em->remove($line);
            else {
                $line->setPrice($item->price);
                $line->setMinPrice($item->minPrice);
                $this->em->persist($line);
            }

        }
        $this->em->flush();
        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idPriceList
     * @return \AppBundle\Soap\Entity\PriceList
     * @throws \SoapFault
     */
    public function getPriceList($username, $password, $idPriceList)
    {
        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $pl = $priceListRepository->find($idPriceList);

        return new \AppBundle\Soap\Entity\PriceList($pl->getId(), $pl->getName(), $pl->getIsDefault());

    }


    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\PriceList[]
     * @throws \SoapFault
     */
    public function listPriceList($username, $password)
    {
        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $pricesList = $priceListRepository->findByParentAgency($agency);
        $return = array();
        foreach ($pricesList as $pl) {
            $return[] = new \AppBundle\Soap\Entity\PriceList($pl->getId(), $pl->getName(), $pl->getIsDefault());
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idPriceList
     * @param int $iduser
     * @return bool
     * @throws \SoapFault
     */
    public function setListPrice($username, $password, $idPriceList, $iduser)
    {
        $allowed = false;

        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        // Utilisateur n'est pas lui même
        if ($userConnected->getId() != $iduser) {
            // L'agence du gestionnaire et de l'utilisateur doit correspondre,
            // ou, le mec est un gestionnaire et l'utilisateur conencté est legrain
            $userToBeUpdated = $userRepository->find($iduser);
            if (!$userToBeUpdated) throw new \SoapFault('serveur', 'Cet utilisateur n\'existe pas');
            if (
                ($userConnected->getRoles()->contains($roleGestionnaire) && $userConnected->getAgency()->getId() == $userToBeUpdated->getAgency()->getId())
                ||
                ($userConnected->getRoles()->contains($roleLegrain) && $userToBeUpdated->getRoles()->contains($roleGestionnaire))
            ) {
                // La grille appartient à l'agence du demanceur
                $priceList = $priceListRepository->find($idPriceList);
                if (!$priceList) throw new \SoapFault('serveur', 'Cette liste n\'existe pas');
                if ($priceList->getParentAgency()->getId() == $userConnected->getAgency()->getId()) {
                    // Si legrain, ou si facturationParLegrain=false
                    if (!$userConnected->getAgency()->getFacturationBylegrain() || $userConnected->getRoles()->contains($roleLegrain)) {
                        $allowed = true;
                    }
                }

            }
        }
        if (!$allowed) throw new \SoapFault('serveur', 'Accès refusé');
        // On affecte la liste à l'utilisateur
        $userToBeUpdated->setPriceList($priceList);
        $this->em->persist($userToBeUpdated);
        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param bool $isDefault
     * @return \AppBundle\Soap\Entity\PriceList
     * @throws \SoapFault
     */
    public function createPriceList($username, $password, $name, $isDefault)
    {
        $allowed = false;

        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($userConnected->getRoles()->contains($roleLegrain)) $allowed = true;
        elseif ($userConnected->getRoles()->contains($roleGestionnaire)) $allowed = true;
        if (!$allowed) throw new \SoapFault('serveur', 'Accès refusé');
        $priceList = new PriceList();
        $priceList->setParentAgency($agency);
        $priceList->setName($name);
        $priceList->setIsDefault($isDefault);
        $priceList->setIsApplicationDefault(false);
        // On cherche la liste par défaut pour l'utilisateur si is default=true
        if ($isDefault == true) {
            $oldDefaultPriceList = $priceListRepository->findOneBy(array('parentAgency' => $agency, 'isDefault' => true));
            if ($oldDefaultPriceList) {
                $oldDefaultPriceList->setIsDefault(false);
                if ($oldDefaultPriceList->getIsApplicationDefault()) {
                    $oldDefaultPriceList->setIsApplicationDefault(false);
                    $priceList->setIsApplicationDefault(true);
                    $this->em->persist($oldDefaultPriceList);
                }
            }
        }
        $this->em->persist($priceList);
        $this->em->flush();

        return new \AppBundle\Soap\Entity\PriceList($priceList->getId(), $priceList->getName(), $priceList->getId());
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idPriceList
     * @param string $name
     * @param bool $isDefault
     * @return \AppBundle\Soap\Entity\PriceList
     * @throws \SoapFault
     */
    public function updatePriceList($username, $password, $idPriceList, $name, $isDefault)
    {
        $allowed = false;

        $userConnected = $this->login($username, $password);
        // On récupère l'agence de l'utilisateur
        $agency = $userConnected->getAgency();
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($userConnected->getRoles()->contains($roleLegrain)) $allowed = true;
        elseif ($userConnected->getRoles()->contains($roleGestionnaire)) $allowed = true;
        if (!$allowed) throw new \SoapFault('serveur', 'Accès refusé');

        $priceList = $priceListRepository->find($idPriceList);
        $priceList->setName($name);
        $priceList->setIsDefault($isDefault);

        // On cherche la liste par défaut pour l'utilisateur si is default=true
        if ($isDefault == true) {
            $oldDefaultPriceList = $priceListRepository->findOneBy(array('parentAgency' => $agency, 'isDefault' => true));
            if ($oldDefaultPriceList) {
                $oldDefaultPriceList->setIsDefault(false);
                if ($oldDefaultPriceList->getIsApplicationDefault()) {
                    $oldDefaultPriceList->setIsApplicationDefault(false);
                    $priceList->setIsApplicationDefault(true);
                    $this->em->persist($oldDefaultPriceList);
                }
            }
        }
        $this->em->persist($priceList);
        $this->em->flush();

        return new \AppBundle\Soap\Entity\PriceList($priceList->getId(), $priceList->getName(), $priceList->getId());


    }

    /**
     * @param string $username
     * @param string $password
     * @param string $type
     * @return \AppBundle\Soap\Entity\Product[]
     * @throws \SoapFault
     */
    public function listProducts($username, $password, $type)
    {
        $userConnected = $this->login($username, $password);
        $userRepository = $this->em->getRepository('AppBundle:User');
        $productRepository = $this->em->getRepository('AppBundle:Product');

        // On récupère aussi le prix associé à cet utilisateur. ( si aucune liste associé à l'utilisateur. on prend la liste par défaut.
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceList = $priceListRepository->findOneByIsApplicationDefault(true);

        // On charge la liste des produits
        if ($type == "both") {
            $dproducts = $productRepository->findAll();
        } elseif ($type == "sub") {
            $dproducts = $productRepository->findBySousProduit(true);
        } else {
            $dproducts = $productRepository->findBySousProduit(false);
        }
        $products = array();
        foreach ($dproducts as $product) {

            // On récupère le prix du produit associé à la liste. S'il n'est pas renseigné. On prend celui de la liste par défaut.
            $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceList));
            if ($priceListLine == null) throw new \SoapFault('error', 'Aucune règle de prix spécifiée pour le produit : ' . $product->getName());
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
            $productAgencyRepository = $this->em->getRepository('AppBundle:ProductAgency');
            // On récupère aussi le code facturation associé à son agence.
            //( agence legrain : id =1 )
            // SAuf si gestionnaire, ou on récupère celui de legrain si l'achat est pour lui...
            // Utilisateur demandé est-il gestionnaire ?
            // gestionnaire
            $agencyRepository = $this->em->getRepository('AppBundle:Agency');
            $productAgency = $productAgencyRepository->findOneByAgency($agencyRepository->find(1));
            $codeFacturation = $productAgency ? $productAgency->getCodeFacturation() : '';
            $categories = array();
            foreach ($product->getCategories() as $c) {
                $categories[] = new \AppBundle\Soap\Entity\Category($c->getId(), $c->getName(), $c->getIsVisible());
            }
            $products[] = new \AppBundle\Soap\Entity\Product($product->getId(), $product->getName(), $product->getReference(), $product->getCodeLgr(), $product->getShortDescription(), $product->getLongDescription(), $product->getMinPeriod(), $codeFacturation, $priceListLine->getPrice(), $priceListLine->getMinPrice(), $priceListLine->getTvaRate()->getPercent(), $categories
                , null, null, null, null, $product->getActive()
            );
        }
        return $products;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @return \AppBundle\Soap\Entity\Product
     * @throws \SoapFault
     */
    public function getProductPriceByDefault($username, $password, $idProduct)
    {
        $userConnected = $this->login($username, $password);
        $userRepository = $this->em->getRepository('AppBundle:User');

        $productRepository = $this->em->getRepository('AppBundle:Product');
        $product = $productRepository->find($idProduct);
        // On récupère aussi le prix associé à cet utilisateur. ( si aucune liste associé à l'utilisateur. on prend la liste par défaut.
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');


        $priceListDefault = $priceListRepository->findOneByIsApplicationDefault(true);

        $priceList = $priceListDefault;

        // On récupère le prix du produit associé à la liste. S'il n'est pas renseigné. On prend celui de la liste par défaut.
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
        $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceList));

        if ($priceListLine == null) {
            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListDefault));
        }
        if ($priceListLine == null) throw new \SoapFault('error', 'Aucune règle de prix spécifiée pour le produit : ' . $product->getName());

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');

        $productAgencyRepository = $this->em->getRepository('AppBundle:ProductAgency');

        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $productAgency = $productAgencyRepository->findOneByAgency($agencyRepository->find(1));
        $codeFacturation = $productAgency ? $productAgency->getCodeFacturation() : '';


        // Liste des catégories pour ce produit.
        $gcats = $product->getCategories();

        $categories = array();
        foreach ($gcats as $gc) {
            $categories[] = new Category($gc->getId(), $gc->getName(), $gc->getIsVisible());
        }

        $dependancies = array();
        $gds = $product->getDependancies();
        foreach ($gds as $gd) {
            $dependancies[] = new ProductSimplified($gd->getId(), $gd->getName());
        }
        $dependancies = empty($dependancies) ? null : $dependancies;

        $produitsComposes = array();
        $gPCs = $product->getProduitsComposes();
        foreach ($gPCs as $gPC) {
            $produitsComposes[] = new ProductSimplified($gPC->getId(), $gPC->getName());
        }
        $produitsComposes = empty($produitsComposes) ? null : $produitsComposes;

        $dependanciesPerCategories = array();
        $gDCs = $product->getDependanciesPerCategories();
        foreach ($gDCs as $gDC) {
            $dependanciesPerCategories[] = new Category($gDC->getId(), $gDC->getName(), $gDC->getIsVisible());
        }
        $dependanciesPerCategories = empty($dependanciesPerCategories) ? null : $dependanciesPerCategories;

        $dfeatures = $product->getFeatures();
        $features = array();
        foreach ($dfeatures as $key => $value) {
            $features[] = new Feature($key, $value);
        }

        if (empty($features)) $features = null;


        $cgus = array();
        $gcgus = $product->getCgus();
        foreach ($gcgus as $gd) {
            $cgus[] = new CGU($gd->getId(), $gd->getName(), $gd->getContent(), $gd->getUrl());
        }
        $cgus = empty($cgus) ? null : $cgus;


        return new \AppBundle\Soap\Entity\Product($product->getId(), $product->getName(), $product->getReference(), $product->getCodeLgr(), $product->getShortDescription(), $product->getLongDescription(), $product->getMinPeriod(), $codeFacturation, $priceListLine->getPrice(), $priceListLine->getMinPrice(), $priceListLine->getTvaRate()->getPercent(), $categories, $dependancies, $produitsComposes, $dependanciesPerCategories, $features, $product->getActive(), $cgus);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @param string $paramInJson
     * @return bool
     * @throws \SoapFault
     */
    public function updateProduct($username, $password, $idProduct, $paramInJson)
    {
        $userConnected = $this->login($username, $password);

        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');
        $allowed = false;
        // Si legrain, autorisé
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        if ($userConnected->getRoles()->contains($roleLegrain)) $allowed = true;

        if (!$allowed) throw new \SoapFault('error', 'Accès interdit');

        // On charge le produit
        $product = $productRepository->find($idProduct);

        $options = (object)json_decode($paramInJson);

        if (property_exists($options, 'name')) {
            $product->setName($options->name);
        }
        if (property_exists($options, 'codeLgr')) {
            $product->setCodeLgr($options->codeLgr);
        }
        if (property_exists($options, 'shortDescription')) {
            $product->setShortDescription($options->shortDescription);
        }
        if (property_exists($options, 'longDescription')) {
            $product->setLongDescription($options->longDescription);
        }
        if (property_exists($options, 'active')) {
            $product->setActive($options->active);
        }

        if (property_exists($options, 'categories')) {
            // On vide la liste des categories
            $product->getCategories()->clear();
            foreach ($options->categories as $c) {
                // On ajoute les catégories qui sont en parametres
                $product->addCategory($productCategoryRepository->find($c));
            }
        }


        if (property_exists($options, 'dependancies')) {
            // On vide la liste des categories
            $product->getDependancies()->clear();
            foreach ($options->dependancies as $c) {
                // On ajoute les produits qui sont en parametres
                $product->addDependancy($productRepository->find($c));
            }
        }

        $cguRepository = $this->em->getRepository('AppBundle:CGU');
        if (property_exists($options, 'cgus')) {
            // On vide la liste des categories
            $product->getCgus()->clear();
            foreach ($options->cgus as $c) {

                $product->addCgus($cguRepository->find($c));
            }
        }

        if (property_exists($options, 'dependanciesPerCategories')) {
            // On vide la liste des categories
            $product->getDependanciesPerCategories()->clear();
            foreach ($options->dependanciesPerCategories as $c) {
                // On ajoute les catégories qui sont en parametres
                $product->addDependancyPerCategory($productCategoryRepository->find($c));
            }
        }

        if (property_exists($options, 'produitsComposes')) {
            // On vide la liste des categories
            $product->getProduitsComposes()->clear();
            foreach ($options->produitsComposes as $c) {
                // On ajoute les produits qui sont en parametres
                $product->addProduitCompose($productRepository->find($c));
            }
        }


        if (property_exists($options, 'features')) {
            $product->setFeatures($options->features);
        }
        if (property_exists($options, 'sousProduit')) {
            $product->setSousProduit($options->sousProduit);
        }

        $this->em->persist($product);
        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @param int $iduser
     * @return \AppBundle\Soap\Entity\Product
     * @throws \SoapFault
     */
    public function getProduct($username, $password, $idProduct, $iduser)
    {

        $userConnected = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');

        $productAgencyRepository = $this->em->getRepository('AppBundle:ProductAgency');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $userAction = $userRepository->find($iduser);
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $product = $productRepository->find($idProduct);

        // On récupère aussi le prix associé à cet utilisateur. ( si aucune liste associé à l'utilisateur. on prend la liste par défaut.
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceListPerso = $userAction->getPriceList();

        // On cherche le prix pour la liste de l'agence (sauf si gestionnaire).
        $priceListAgency = !$userAction->getRoles()->contains($roleAgence) ? $priceListRepository->findOneBy(array('isDefault' => true, 'parentAgency' => $userAction->getAgency())) : null;
        // $priceListAgency =  $priceListRepository->findOneBy(array('isDefault' => true, 'parentAgency' => $userAction->getAgency()));

        $priceListApplicationDefault = $priceListRepository->findOneByIsApplicationDefault(true);



        $priceDefined = false;
        $priceListLine = null;

        if ($priceListPerso != null && !$priceDefined) {
            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListPerso));
            if ($priceListLine) $priceDefined = true;
        }
        if ($priceListAgency != null && !$priceDefined) {
            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListAgency));
            if ($priceListLine) $priceDefined = true;
        }
        if ($priceListApplicationDefault != null && !$priceDefined) {
            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $product, 'priceList' => $priceListApplicationDefault));

        }


        if ($priceListLine == null) throw new \SoapFault('error', 'Aucune règle de prix spécifiée pour le produit : ' . $product->getName());


        // On récupère aussi le code facturation associé à son agence.
        //( agence legrain : id =1 )
        // SAuf si gestionnaire, ou on récupère celui de legrain si l'achat est pour lui...
        // Utilisateur demandé est-il gestionnaire ?
        // gestionnaire
        if ($userAction->getAgency()->getId() != 1 && $userAction->getRoles()->contains($roleAgence)) {
            $productAgency = $productAgencyRepository->findOneByAgency($userAction->getAgency());
        } else {
            $agencyRepository = $this->em->getRepository('AppBundle:Agency');
            $productAgency = $productAgencyRepository->findOneByAgency($agencyRepository->find(1));

        }
        $codeFacturation = $productAgency ? $productAgency->getCodeFacturation() : '';


        $features = array();
        foreach ($product->getFeaturesAsArray() as $key => $value) {
            $features[] = new Feature($key, $value);
        }

        //$id, $name, $reference, $codeLgr,
        // $shortDescription, $longDescription, $minPeriod, $codeFacturationAgence, $priceHT,
        // $minPriceHT, $percentTax,$categories=null,$dependancies=null,$produitsComposes=null,$dependanciesPerCategories=null,$features=null,
        //$active=null,$cgus=null
        $return = new \AppBundle\Soap\Entity\Product($product->getId(), $product->getName(), $product->getReference(), $product->getCodeLgr(),
            $product->getShortDescription(), $product->getLongDescription(), $product->getMinPeriod(), $codeFacturation, $priceListLine->getPrice(),
            $priceListLine->getMinPrice(), $priceListLine->getTvaRate()->getPercent(), null, null, null, null, $features,
            $product->getActive(), null
        );
        // $this->logger->info('passage dans get Product');
        // throw new \SoapFault('e',json_encode($return));
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idCart
     * @param int $idUserWhoPaid
     * @return string jsonArray
     */
    public function getTotalCartPerUserWhoPaid($username,$password,$idCart,$idUserWhoPay){
        // on boucle sur les lignes pour récupérer le nouveau prix (on supprime aussi le prix des produits hébergements si le idUser... est gestionnaire (sauf si le serveur appartient à legrain)
        // pas de modifs des prix, uniquement affichage
        // Tenir compte des agences non assujeti à la tva (uniquement client final)
        // Retour d'u json avec le HT, la tva et le ttc
        $userConnected = $this->login($username, $password);
        $userRepository = $this->em->getRepository('AppBundle:User');
        $cartRepository = $this->em->getRepository('AppBundle:Cart');

        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $userWhoPay = $userRepository->find($idUserWhoPay);
        $cart = $cartRepository->find($idCart);

        // On regarde si le panier peut être payé par le idUserWhoPay
        $error = true;
        if($userConnected->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN')))$error=false;
        elseif($cart->getUser()->getId()==$userWhoPay->getId())$error=false;
        elseif($userWhoPay->getParent()&&$userWhoPay->getParent()->getId()==$userConnected->getId())$error = false;

        if($error)throw new \SoapFault('accès interdit','Accès interdit');

        $totalHt=(float)0;
        $totalTVA = (float)0;
        foreach($cart->getCartLines() as $line){
            //$username, $password, $idProduct, $iduser
            $newProduct = $this->getProduct($username, $password,$line->getProduct()->getId() ,$userWhoPay->getId() );
            //$product,$options,User $userConnected,$username,$password,$iduserRequest,$libel=''
            $res = json_decode($this->calculPriceLine($newProduct,json_decode($line->getOptions()) ,$userConnected , $username, $password,$idUserWhoPay));
            //  throw new \SoapFault('e',json_encode($res));
            $totalHt+=(float)$res->totalHt;
            $totalTVA += (float)$res->totalTax;

        }
        //       throw new \SoapFault('e',$totalHt);
        $totalTTC = $totalHt+$totalTVA;
        return json_encode(array('totalHt'=>$totalHt,'totalTva'=>$totalTVA,'totalTTC'=>$totalTTC));



    }
    /**
     * @param string $username
     * @param string $password
     * @param string $type
     * @param int $idUser
     * @param float $amount
     * @param string|null $label
     * @param string|null $cardNumber
     * @param string|null $cardExpirationMonth
     * @param string|null $cardExpirationYear
     * @param string|null $cvc
     * @param string|null $cardFullName
     * @return bool
     * @throws \SoapFault
     */
    public function creditAnotherAccount($username, $password, $type, $idUser, $amount, $label = null, $cardNumber = null, $cardExpirationMonth = null, $cardExpirationYear = null, $cvc = null, $cardFullName = null)
    {
        $userConnected = $this->login($username, $password);

        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $userToBeCredited = $userRepository->find($idUser);
        if (!$userToBeCredited) throw new \SoapFault('Error', 'Utilisateur introuvable');
        $allowed = false;
        // Si legrain, autorisé
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($userConnected->getRoles()->contains($roleLegrain)) $allowed = true;
        elseif ($userConnected->getRoles()->contains($roleAgence)) {
            // On regarde si l'utilisateurà crediter fait parti de l'agence de l'utilisateur agence ( gestionnaire)
            if ($userToBeCredited->getAgency()->getId() == $userConnected->getAgency()->getId()) $allowed = true;
        } elseif ($userToBeCredited->getId() == $userConnected->getId()) $allowed = true;

        if (!$userConnected->getRoles()->contains($roleLegrain) && ($userToBeCredited->getId() == $userConnected->getId()) && $type != 'card') throw new \SoapFault('ERROR', 'Impossible de créditer votre propre compte autrement que par carte bancaire');


        // Vérification
        if (!$allowed) throw new \SoapFault('ERROR', 'Autorisation insufisante');
        if (!$this->accountBalanceExist($username, $password, $userToBeCredited->getId()))
            throw new \SoapFault('PAS_DE_COMPTE', 'Vous ne pouvez pas créditer un compte inexistant');
        if ($amount < 10) throw new \SoapFault('ERROR', 'Le montant minimum que vous pouvez créditer sur le compte est de 10 euros');

        switch ($type) {
            case 'card':
                // Si gestionnaire ou si l'agence ne facture pas
                if ($userConnected->getParent() == null || $userConnected->getAgency()->getFacturationBylegrain()) {

                    // On loade l'agence Legrain (1)
                    $agency = $agencyRepository->find(1);
                } else {
                    $agency = $userConnected->getAgency();
                }
                if ($agency->getStripeKey() == null) throw new \SoapFault('error', 'Aucun compte stripe n\'est parametré');
                $description = 'Rechargement compte pré payé';
                // Appel stripe ( clef publique à parametrer)
                // \Stripe\Stripe::setApiKey('sk_test_gSTXDQxFFZiiBdKOGlrcLZWh');
                try {
                    \Stripe\Stripe::setApiKey($agency->getStripeKey());
                    $myCard = array('number' => $cardNumber, 'exp_month' => $cardExpirationMonth, 'exp_year' => $cardExpirationYear, 'cvc' => $cvc, 'name' => $cardFullName);
                    // Amount en centime
                    $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => ($amount * 100), 'currency' => 'eur'));
                } catch (\Stripe\Error\Card $e) {
                    // Since it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $err = $body['error'];
                    switch ($err['code']) {

                        case 'incorrect_cvc':
                            $error = "le code de sécurité est incorrect";
                            break;
                        case 'expired_card':
                            $error = "la carte a expiré";
                            break;
                        case 'invalid_cvc':
                            $error = "Le code de sécurité de la carte est invalide";
                            break;
                        case 'invalid_expiry_month':
                            $error = "Le mois d'expiration de la carte est invalide";
                            break;
                        case 'invalid_expiry_year':
                            $error = "L'année d'expiration de la carte est invalide";
                            break;
                        case 'invalid_number':
                        case 'incorrect_number':
                            $error = 'Le numéro de carte saisie est invalide';
                            break;
                        default:
                            $error = 'Une erreur est survenue, rééssayez ultérieurement';
                            break;
                    }
                    throw new \SoapFault('server', $error);
                } catch (\Stripe\Error\InvalidRequest $e) {
                    // Invalid parameters were supplied to Stripe's API
                    throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

                } catch (\Stripe\Error\Authentication $e) {
                    // Authentication with Stripe's API failed
                    // (maybe you changed API keys recently)
                    throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

                } catch (\Stripe\Error\ApiConnection $e) {
                    // Network communication with Stripe failed
                    throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

                } catch (\Stripe\Error\Base $e) {
                    // Display a very generic error to the user, and maybe send
                    // yourself an email
                    throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

                }
                if ($charge->status != "succeeded") throw new \SoapFault('error', 'Erreur au moment du paiement');
                if (!$charge->paid) throw new \SoapFault('error', 'Erreur au moment du paiement');
                $idTransactionExterne = $charge->id;
                break;
            default:
                $description = $label;
                $idTransactionExterne = 'Cheque,espèce ou virement';
                break;
        }

        // On récupère le compte pré payé
        $accountBalanceRepository = $this->em->getRepository('AppBundle:AccountBalance');
        $accountBalance = $accountBalanceRepository->findOneByUser($userToBeCredited);
        if ($accountBalance == null) {
            $accountBalance = new AccountBalance();
            $accountBalance->setUser($userConnected);
            $accountBalance->setAgency($userConnected->getAgency());
            $accountBalance->setAmount(0);
            $this->em->persist($accountBalance);
            $this->em->flush();

        }
        $line = new AccountBalanceLine();
        $line->setDescription($description);
        $line->setMouvement($amount);
        $line->setIdPaypal($idTransactionExterne);
        // On ajoute le mouvement.
        $accountBalance->addLine($line);
        $this->em->persist($line);
        $this->em->flush();
        // On retourne true;
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $cardNumber
     * @param string $cardExpirationMonth
     * @param string $cardExpirationYear
     * @param string $cvc
     * @param string $cardFullName
     * @param float $amount
     * @return bool
     * @throws \SoapFault
     */
    public function creditAccount($username, $password, $cardNumber, $cardExpirationMonth, $cardExpirationYear, $cvc, $cardFullName, $amount)
    {
        $userConnected = $this->login($username, $password);
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        if (!$this->accountBalanceExist($username, $password, $userConnected->getId()))
            throw new \SoapFault('PAS_DE_COMPTE', 'Vous ne pouvez pas créditer un compte inexistant');


        if ($amount < 10) throw new \SoapFault('ERROR', 'Le montant minimum que vous pouvez créditer sur votre compte est de 10 euros');
        $description = 'Rechargement compte pré payé';
        // Appel stripe ( clef publique à parametrer)

        // Si gestionnaire ou si l'agence ne facture pas
        if ($userConnected->getParent() == null || $userConnected->getAgency()->getFacturationBylegrain()) {
            // On loade l'agence Legrain (1)
            $agency = $agencyRepository->find(1);
        } else {
            $agency = $userConnected->getAgency();
        }

        if ($agency->getStripeKey() == null) throw new \SoapFault('error', 'Aucun compte stripe n\'est parametré');

        try {
            // récupèration de la clef stripe.
            // On charge l'agence du tiers. S'il est gestionnaire. C'est l'agence legrain qui doit l'être.


            \Stripe\Stripe::setApiKey($agency->getStripeKey());
            $myCard = array('number' => $cardNumber, 'exp_month' => $cardExpirationMonth, 'exp_year' => $cardExpirationYear, 'cvc' => $cvc, 'name' => $cardFullName);
            // Amount en centime
            $charge = \Stripe\Charge::create(array('card' => $myCard, 'amount' => ($amount * 100), 'currency' => 'eur'));
        } catch (\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err = $body['error'];
            switch ($err['code']) {

                case 'incorrect_cvc':
                    $error = "le code de sécurité est incorrect";
                    break;
                case 'expired_card':
                    $error = "la carte a expiré";
                    break;
                case 'invalid_cvc':
                    $error = "Le code de sécurité de la carte est invalide";
                    break;
                case 'invalid_expiry_month':
                    $error = "Le mois d'expiration de la carte est invalide";
                    break;
                case 'invalid_expiry_year':
                    $error = "L'année d'expiration de la carte est invalide";
                    break;
                case 'invalid_number':
                case 'incorrect_number':
                    $error = 'Le numéro de carte saisie est invalide';
                    break;
                default:
                    $error = 'Une erreur est survenue, rééssayez ultérieurement';
                    break;
            }
            throw new \SoapFault('server', $error);
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            throw new \SoapFault('server', $e->getMessage());
            throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            throw new \SoapFault('server', $e->getMessage());
            throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            throw new \SoapFault('server', $e->getMessage());
            throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            throw new \SoapFault('server', $e->getMessage());
            throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            throw new \SoapFault('server', $e->getMessage());
            throw new \SoapFault('server', 'Une erreur est survenue, merci de contacter votre agence');

        }
        if ($charge->status != "succeeded") throw new \SoapFault('error', 'Erreur au moment du paiement');
        if (!$charge->paid) throw new \SoapFault('error', 'Erreur au moment du paiement');
        // On récupère le compte pré payé
        $accountBalanceRepository = $this->em->getRepository('AppBundle:AccountBalance');
        $accountBalance = $accountBalanceRepository->findOneByUser($userConnected);
        if ($accountBalance == null) {
            $accountBalance = new AccountBalance();
            $accountBalance->setUser($userConnected);
            $accountBalance->setAgency($userConnected->getAgency());
            $accountBalance->setAmount(0);
            $this->em->persist($accountBalance);
            $this->em->flush();

        }
        $line = new AccountBalanceLine();
        $line->setDescription($description);
        $line->setMouvement($amount);
        $line->setIdPaypal($charge->id);
        // On ajoute le mouvement.
        $accountBalance->addLine($line);
        $this->em->persist($line);
        $this->em->flush();
        // On retourne true;
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @return bool
     * @throws \SoapFault
     */
    public function accountBalanceExist($username, $password, $iduser)
    {
        return true;
        /*
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agencylegrain = $agencyRepository->find(1);
        $userRequest = $userRepository->find($iduser);
        // récuperation possible pour ROLE_LEGRAIN, sauf pour son propre compte, ROLE_AGENCE (son agence uniquement), ROLE_UTILISATEUR_AGENCE ( son compte
        // uniquement et pour le moment s'il est client de l'agence legrain )
        $error = true;

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        // Si compte email
        if ($userRequest->getRoles()->contains($roleCompteEmail)) {
            $error = true;
            // Si agence legrain ou gestionnaire d'une agence
        } elseif (($userRequest->getAgency()->getId() == $agencylegrain->getId()) || $userRequest->getRoles()->contains($roleAgency)) {
            // Si legrain, pas de problème sauf pour son propre compte
            if ($userConnected->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                // Si l'utilisateur est connecté pour lui même
                if ($userRequest->getId() == $userConnected->getId()) {
                    $error = false;
                }
            }
        }

        return !$error;
        */
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @return bool
     * @throws \SoapFault
     */
    public function cartExist($username, $password, $iduser)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agencylegrain = $agencyRepository->find(1);
        $userRequest = $userRepository->find($iduser);
        // récuperation possible pour ROLE_LEGRAIN, sauf pour son propre compte, ROLE_AGENCE (son agence uniquement), ROLE_UTILISATEUR_AGENCE ( son compte
        // uniquement et pour le moment s'il est client de l'agence legrain )
        $error = true;

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        // Si compte email
        if ($userRequest->getRoles()->contains($roleCompteEmail)) {
            $error = true;
            // Si agence legrain ou gestionnaire d'une agence
        }/* elseif (($userRequest->getAgency()->getId() == $agencylegrain->getId()) || $userRequest->getRoles()->contains($roleAgency)) {
            // Si legrain, pas de problème sauf pour son propre compte
            if ($userConnected->getRoles()->contains($roleLegrain)) {
                // if($userConnected->getId() != $userRequest->getId()) {
                $error = false;
            } else {
                // Si l'utilisateur est connecté pour lui même
                if ($userRequest->getId() == $userConnected->getId()) {
                    $error = false;
                }
            }
        }
*/
        else {
            $error = false;
        }
        return !$error;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idcart
     * @return string
     * @throws \SoapFault
     */
    public function getCreationServerInCart($username, $password, $idcart)
    {
        $userConnected = $this->login($username, $password);
        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');


        $productCategory = $productCategoryRepository->findOneByName('instance');
        $cart = $cartRepository->find($idcart);
        if (!$cart) throw new \SoapFault('Ce panier n\'existe pas ou plus.');

        // On cherche les lignes du panier qui possède un produit qui appartient à la catégorie : createndd
        $lines = $cartLineRepository->findLinesPerProductCategory($cart, $productCategory);
        $return = array();
        foreach ($lines as $line) {
            $p = $line->getProduct();


            $options = json_decode($line->getOptions());


            $return[] = array(
                'idLine' => $line->getId(),
                'iduser' => $line->getUtilisateurPourLequelEstLeProduit()->getId(),
                'name' => $line->getProductName(),
                'alreadyOk' => (property_exists($options, 'lineInCart') ? true : false)

            );
        }
        return json_encode($return);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idcart
     * @return string
     * @throws \SoapFault
     */
    public function getCreationHebergementInCart($username, $password, $idcart)
    {
        $userConnected = $this->login($username, $password);
        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');


        $productCategory = $productCategoryRepository->findOneByName('produit_generique_instance_mutualisable');
        $cart = $cartRepository->find($idcart);
        if (!$cart) throw new \SoapFault('Ce panier n\'existe pas ou plus.');

        // On cherche les lignes du panier qui possède un produit qui appartient à la catégorie : createndd
        $lines = $cartLineRepository->findLinesPerProductCategory($cart, $productCategory);
        $return = array();
        foreach ($lines as $line) {
            $p = $line->getProduct();


            $options = json_decode($line->getOptions());


            $return[] = array(
                'idLine' => $line->getId(),
                'iduser' => $line->getUtilisateurPourLequelEstLeProduit()->getId(),
                'name' => $line->getProductName(),
                'alreadyOk' => (property_exists($options, 'idHosting') ? true : false)

            );
        }
        return json_encode($return);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idcart
     * @return string
     * @throws \SoapFault
     */
    public function getCreationNddInCart($username, $password, $idcart)
    {
        $userConnected = $this->login($username, $password);
        $cartRepository = $this->em->getRepository('AppBundle:Cart');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');
        $productCategoryRepository = $this->em->getRepository('AppBundle:ProductCategory');


        $productCategory = $productCategoryRepository->findOneByName('createndd');
        $cart = $cartRepository->find($idcart);
        if (!$cart) throw new \SoapFault('Ce panier n\'existe pas ou plus.');

        // On cherche les lignes du panier qui possède un produit qui appartient à la catégorie : createndd
        $lines = $cartLineRepository->findLinesPerProductCategory($cart, $productCategory);
        $return = array();
        foreach ($lines as $line) {
            $p = $line->getProduct();


            $options = json_decode($line->getOptions());


            $return[] = array(
                'idLine' => $line->getId(),
                'contact' => $options->contact,
                'ndd' => $options->ndd,
                'alreadyOk' => (property_exists($options, 'lineInCart') ? true : false)
            );
        }
        return json_encode($return);
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idCartLine
     * @return \AppBundle\Soap\Entity\CartLine
     * @throws \SoapFault
     */
    public function getCartLine($username, $password, $idCartLine)
    {

        $userConnected = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $cartLineRepository = $this->em->getRepository('AppBundle:CartLine');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');

        $error = true;

        $cartLine = $cartLineRepository->find($idCartLine);

        if (!$userConnected->getRoles()->contains($roleLegrain)) {
            // Si la ligne lui appartient
            if ($userConnected->getId() == $cartLine->getCart()->getUser()->getId()) {
                $error = false;
                // Si role agence et que la ligne appartient à un de ses fils

            } elseif ($userConnected->getRoles()->contains($roleAgence) && $userConnected->getAgency()->getId() == $cartLine->getCart()->getUser()->getAgency()->getId()) {
                $error = false;
            }
        } else {
            $error = false;
        }
        if ($error) throw new \SoapFault('e', 'Autorisations insufisantes pour afficher ce contact');

        return new \AppBundle\Soap\Entity\CartLine($cartLine->getId(), $cartLine->getProductReference(), $cartLine->getProductName(), $cartLine->getQuantity(), $cartLine->getUnitPrice(), $cartLine->getPercentTax(), $cartLine->getTotalHt(), $cartLine->getTotalTax(), $cartLine->getProduct()->getId());

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @return \AppBundle\Soap\Entity\Cart
     * @throws \SoapFault
     */
    public function getCart($username, $password, $iduser)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agencylegrain = $agencyRepository->find(1);
        $userRequest = $userRepository->find($iduser);
        // récuperation possible pour ROLE_LEGRAIN, ROLE_AGENCE (son agence uniquement), ROLE_UTILISATEUR_AGENCE ( son compte
        // uniquement et pour le moment s'il est client de l'agence legrain )
        $error = true;

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        // Si compte email
        if ($userRequest->getRoles()->contains($roleCompteEmail)) {
            $error = true;
            // Si agence legrain ou gestionnaire d'une agence
        } else {
            $error = false;
        }
        /* elseif (($userRequest->getAgency()->getId() == $agencylegrain->getId()) || $userRequest->getRoles()->contains($roleAgency)) {
            // Si legrain, pas de  sauf pour son propre compte
            if ($userConnected->getRoles()->contains($roleLegrain)) {
                // if($userConnected->getId() != $userRequest->getId()) {
                $error = false;
                // }
            } else {
                // Si l'utilisateur est connecté pour lui même
                if ($userRequest->getId() == $userConnected->getId()) {
                    $error = false;
                }
            }
        }
*/
        if ($error) throw new \SoapFault('FORBIDDEN', 'Accès interdit, ou, l\'utilisateur ne peut pas avoir de panier');

        // Si l'utilisateur n'a pas de panier payé à O. On en créé un
        $cartRepository = $this->em->getRepository('AppBundle:Cart');

        $cart = $cartRepository->findOneby(array('user' => $userRequest, 'isPaid' => 0));

        if ($cart == null) {

            $cart = new Cart();
            $cart->setUser($userRequest);

            $this->em->persist($cart);
            $this->em->flush();

        }


        // Si
        $tmp = array();
        $usersTmp = array();
        $agencesTmp = array();
        foreach ($cart->getCartLines() as $l) {
            $usersTmp[$l->getUtilisateurPourLequelEstLeProduit()->getId()] = $l->getUtilisateurPourLequelEstLeProduit();
            $agencesTmp[$l->getUtilisateurPourLequelEstLeProduit()->getAgency()->getId()] = $l->getUtilisateurPourLequelEstLeProduit()->getAgency();
            $tmp[] = new \AppBundle\Soap\Entity\CartLine($l->getId(), $l->getProductReference(), $l->getProductName(), $l->getQuantity(), $l->getUnitPrice(), $l->getPercentTax(), $l->getTotalHt(), $l->getTotalTax(), $l->getProduct()->getId());


            // Cgus associées.
            $cgus = array();
            $cgusAdded = array();
            $product = $l->getProduct();
            foreach ($product->getCgus() as $cgu) {

                if (!in_array($cgu->getId(), $cgusAdded)) {
                    $cgusAdded[$cgu->getId()] = $cgu->getId();
                    $cgus[] = new CGU($cgu->getId(), $cgu->getName(), $cgu->getContent(), $cgu->getUrl());
                }
            }


        }

        $potentialPayers = array();
        $idsPotentialsPayers = array();


// Si legrain
        if ($userConnected->getRoles()->contains($roleLegrain)) {
            if (!in_array($userConnected->getId(), $idsPotentialsPayers)) {
                $idsPotentialsPayers[] = $userConnected->getId();
                $potentialPayers[] = new \AppBundle\Soap\Entity\PotentialPayer($userConnected->getId(), $userConnected->getName(), $userConnected->getFirstname(), $userConnected->getEmail(), $userConnected->getAddress1(), $userConnected->getAddress2(), $userConnected->getAddress3(),
                    new \AppBundle\Soap\Entity\City($userConnected->getCity()->getId(), $userConnected->getCity()->getName(), $userConnected->getCity()->getCodeInsee()),
                    new \AppBundle\Soap\Entity\ZipCode($userConnected->getZipcode()->getId(), $userConnected->getZipcode()->getName()),
                    $userConnected->getPhone(), $userConnected->getActive(),
                    // new \AppBundle\Soap\Entity\Agency($userConnected->getAgency()->getId(), $userConnected->getAgency()->getName(), $userConnected->getAgency()->getSiret(), $userConnected->getAgency()->getAddress1(), $userConnected->getAgency()->getAddress2(), $userConnected->getAgency()->getAddress1(),

                    //new \AppBundle\Soap\Entity\City($userConnected->getAgency()->getCity()->getId(), $userConnected->getAgency()->getCity()->getName(), $userConnected->getAgency()->getCity()->getCodeInsee()),
                    //new \AppBundle\Soap\Entity\ZipCode($userConnected->getAgency()->getZipCode()->getId(), $userConnected->getAgency()->getZipCode()->getName()),
                    //$userConnected->getAgency()->getPhone(), $userConnected->getAgency()->getEmail(), $userConnected->getAgency()->getWebsite(), $userConnected->getAgency()->getFacturationBylegrain(), $userConnected->getAgency()->getInfosCheque(), $userConnected->getAgency()->getInfosVirement()),
                    $this->_getAgency($userConnected->getAgency()),
                    $userConnected->getAccountBalance()->getAmount()
                );
            }
        }


        // Agence si il y en a une seule dans le panier.

        if (count($agencesTmp) == 1) {
            // Si agence ou legrain ( car klegrain possède ROLE_AGENCE)
            if ($userConnected->getRoles()->contains($roleAgency)) {

                foreach ($agencesTmp as $a) {

                    $gestionnaire = $userRepository->findParentByAgency($a);
                    //  throw new \SoapFault('test','test');

                    if (!$gestionnaire->getRoles()->contains($roleLegrain)) {

                        $accountBalance = $gestionnaire->getAccountBalance();
                        if (!$accountBalance) {
                            // Dans le cas ou le gestionnaire ne s'est jamais connecté, il n'a pas encore de "accountBalance", il faut donc lui en creer un.
                            $accountBalance = new AccountBalance();
                            $accountBalance->setUser($gestionnaire);
                            $accountBalance->setAgency($gestionnaire->getAgency());
                            $accountBalance->setAmount(0);
                            $gestionnaire->setAccountBalance($accountBalance);
                            $this->em->persist($accountBalance);
                            $this->em->persist($gestionnaire);
                            $this->em->flush();
                        }

                        if (!in_array($gestionnaire->getId(), $idsPotentialsPayers)) {
                            $idsPotentialsPayers[] = $gestionnaire->getId();
                            $potentialPayers[] = new \AppBundle\Soap\Entity\PotentialPayer($gestionnaire->getId(), $gestionnaire->getName(), $gestionnaire->getFirstname(), $gestionnaire->getEmail(), $gestionnaire->getAddress1(), $gestionnaire->getAddress2(), $gestionnaire->getAddress3(),
                                new \AppBundle\Soap\Entity\City($gestionnaire->getCity()->getId(), $gestionnaire->getCity()->getName(), $gestionnaire->getCity()->getCodeInsee()),
                                new \AppBundle\Soap\Entity\ZipCode($gestionnaire->getZipcode()->getId(), $gestionnaire->getZipcode()->getName()),
                                $gestionnaire->getPhone(), $gestionnaire->getActive(),
                                /*

                                 new \AppBundle\Soap\Entity\Agency($gestionnaire->getAgency()->getId(), $gestionnaire->getAgency()->getName(), $gestionnaire->getAgency()->getSiret(), $gestionnaire->getAgency()->getAddress1(), $gestionnaire->getAgency()->getAddress2(), $gestionnaire->getAgency()->getAddress1(),
                                    new \AppBundle\Soap\Entity\City($gestionnaire->getAgency()->getCity()->getId(), $gestionnaire->getAgency()->getCity()->getName(), $gestionnaire->getAgency()->getCity()->getCodeInsee()),
                                    new \AppBundle\Soap\Entity\ZipCode($gestionnaire->getAgency()->getZipCode()->getId(), $gestionnaire->getAgency()->getZipCode()->getName()),
                                    $gestionnaire->getAgency()->getPhone(), $gestionnaire->getAgency()->getEmail(), $gestionnaire->getAgency()->getWebsite(),$gestionnaire->getAgency()->getFacturationBylegrain(),$gestionnaire->getAgency()->getInfosCheque(),$gestionnaire->getAgency()->getInfosVirement()),
                                */
                                $this->_getAgency($gestionnaire->getAgency()),
                                $gestionnaire->getAccountBalance()->getAmount()
                            );
                        }
                    }
                }
            }
        }

        if (count($usersTmp) == 1) {
            foreach ($usersTmp as $u) {
                // 1 étant l'id de l'agence Legrain
                //  if ($u->getAgency()->getId() == 1) {
                if (!$u->getRoles()->contains($roleLegrain)) {


                    $accountBalance = $u->getAccountBalance();
                    if (!$accountBalance) {
                        // Dans le cas ou l'utilisateur ne s'est jamais connecté, il n'a pas encore de "accountBalance", il faut donc lui en creer un.
                        $accountBalance = new AccountBalance();
                        $accountBalance->setUser($u);
                        $accountBalance->setAgency($u->getAgency());
                        $accountBalance->setAmount(0);
                        $u->setAccountBalance($accountBalance);
                        $this->em->persist($accountBalance);
                        $this->em->persist($u);
                        $this->em->flush();
                    }
                    if (!in_array($u->getId(), $idsPotentialsPayers)) {
                        $idsPotentialsPayers[] = $u->getId();
                        $potentialPayers[] = new \AppBundle\Soap\Entity\PotentialPayer($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(),
                            new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee()),
                            new \AppBundle\Soap\Entity\ZipCode($u->getZipcode()->getId(), $u->getZipcode()->getName()),
                            $u->getPhone(), $u->getActive(),
                            /* new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress1(),
                                 new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()),
                                 new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()),
                                 $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(), $u->getAgency()->getFacturationBylegrain(), $u->getAgency()->getInfosCheque(), $u->getAgency()->getInfosVirement()),
                            */
                            $this->_getAgency($u->getAgency()),

                            $u->getAccountBalance()->getAmount()
                        );
                    }
                }
                //  }
            }
        }


        $accountBalanceLine = null;
        if ($a = $cart->getAccountBalanceLine()) {
            $accountBalanceLine = new \AppBundle\Soap\Entity\AccountBalanceLine($a->getId(), $a->getDate(), $a->getIdTransaction(), $a->getDescription(), $a->getMouvement(), $a->getBalance());
        }
        $cgus = empty($cgus) ? null : $cgus;
        $tmp = empty($tmp) ? null : $tmp;
        return new \AppBundle\Soap\Entity\Cart($cart->getId(), $cart->getTotalHt(), $cart->getTotalTax(), $cart->getIsPaid(), $cart->getDateIsPaid(), $accountBalanceLine, $tmp, $potentialPayers, $cgus);

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $infosCheque
     * @param string $infosVirements
     * @return bool
     * @throws \SoapFault
     */
    public function updateParamPaiements($username, $password, $infosCheque, $infosVirements)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');


        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $agency = $userConnected->getAgency();
        // Si pas gestionnaire = ban
        if (!$userConnected->getRoles()->contains($roleAgence)) throw new \SoapFault('error', 'Accès interdit');

        // Si gestion par legrain (sauf si legrain) = ban
        if (!$userConnected->getRoles()->contains($roleLegrain)) {
            if ($agency->getFacturationBylegrain()) throw new  \SoapFault('error', 'Accès interdit');
        }

        // Update des infos
        $agency->setInfosCheque($infosCheque);
        $agency->setInfosVirement($infosVirements);
        $this->em->persist($agency);

        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return string
     * @throws \SoapFault
     */
    public function getParamsPaiement($username, $password)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');


        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        // si gestionnaire, on charge l'agence Lergrain (1)
        // Si utilisateur, on regarde si l'agence est facturé par legrain. Si oui, on charge legrain, si non, on charge l'agence de l'utilisateur
        if ($userConnected->getRoles()->contains($roleAgence) || $userConnected->getAgency()->getFacturationBylegrain() === null || $userConnected->getAgency()->getFacturationBylegrain()) {
            $agencyRepository = $this->em->getRepository('AppBundle:Agency');
            $agency = $agencyRepository->find(1);
        } else {
            $agency = $userConnected->getAgency();
        }

        // return en json
        return json_encode(array('cheque' => $agency->getInfosCheque(), 'virement' => $agency->getInfosVirement()));
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @return \AppBundle\Soap\Entity\AccountBalance
     * @throws \SoapFault
     */
    public function getAccountBalance($username, $password, $iduser)
    {
        $userConnected = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agencylegrain = $agencyRepository->find(1);
        $userRequest = $userRepository->find($iduser);
        // récuperation possible pour ROLE_LEGRAIN, ROLE_AGENCE (son agence uniquement), ROLE_UTILISATEUR_AGENCE ( son compte
        // uniquement et pour le moment s'il est client de l'agence legrain )
        /*
         $error = true;

         $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
         $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
         $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
         // Si compte email
         if ($userRequest->getRoles()->contains($roleCompteEmail)) {
             $error = true;
             // Si agence legrain ou gestionnaire d'une agence
         } elseif (($userRequest->getAgency()->getId() == $agencylegrain->getId()) || $userRequest->getRoles()->contains($roleAgency)) {
             // Si legrain, pas de  sauf pour son propre compte
             if ($userConnected->getRoles()->contains($roleLegrain)) {
                 //    if($userConnected->getId() != $userRequest->getId()) {
                 $error = false;
                 //         }
             } else {
                 // Si l'utilisateur est connecté pour lui même
                 if ($userRequest->getId() == $userConnected->getId()) {
                     $error = false;
                 }
             }
         }

         if ($error) throw new \SoapFault('FORBIDDEN', 'Accès interdit, ou, l\'utilisateur ne peut pas avoir de compte');
         */
        // Si l'utilisateur n'a pas de compte, on le créé avec un solde à 0
        $accountBalanceRepository = $this->em->getRepository('AppBundle:AccountBalance');
        $accountBalance = $accountBalanceRepository->findOneByUser($userRequest);
        if ($accountBalance == null) {

            $accountBalance = new AccountBalance();
            $userRequest->setAccountBalance($accountBalance);
            $accountBalance->setUser($userRequest);
            $accountBalance->setAgency($userRequest->getAgency());
            $accountBalance->setAmount(0);

            $this->em->persist($accountBalance);
            $this->em->persist($userRequest);
            $this->em->flush();

        }
        $tmp = array();
        foreach ($accountBalance->getLines() as $l) {
            $tmp[] = new \AppBundle\Soap\Entity\AccountBalanceLine($l->getId(), $l->getDate(), $l->getTransaction()->getId(), $l->getDescription(), $l->getMouvement(), $l->getBalance());
        }
        $tmp = empty($tmp) ? null : $tmp;
        return new \AppBundle\Soap\Entity\AccountBalance($accountBalance->getId(), $accountBalance->getAmount(), $tmp);

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $emailForward
     * @return mixed
     * @throws \SoapFault
     */
    public function deleteMailForward($username, $password, $emailForward)
    {

        $tmp = explode("@", $emailForward);
        //$loginEmail=$tmp[0];
        $nameEmail = $tmp[0];
        $domain = $tmp[1];

        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            if (!$user->getRoles()->contains($roleLegrain)) {
                $error = true;
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
                if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');
            }

            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $log = new Log($user, 'Suppression de la redirection : ' . $emailForward);

            $this->em->persist($log);
            $this->em->flush();
            return $gandiApi->deleteMailForward($connect, $domain, $nameEmail);


        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $source
     * @param mixed $destinations
     * @return \GandiBundle\Entity\Mailbox\ForwardReturn
     * @throws \SoapFault
     */
    public function updateMailForward($username, $password, $source, $destinations)
    {
        $tmp = explode("@", $source);
        //$loginEmail=$tmp[0];
        $nameEmail = $tmp[0];
        $domain = $tmp[1];

        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
            if (!$user->getRoles()->contains($roleLegrain)) {
                // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
                $error = true;
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
                if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');
            }

            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $log = new Log($user, 'Modification  de la redirection : ' . $source);

            $this->em->persist($log);
            $this->em->flush();
            return $gandiApi->updateMailForward($connect, $domain, $nameEmail, $destinations);


        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $source
     * @param mixed $destinations
     * @return \GandiBundle\Entity\Mailbox\ForwardReturn
     * @throws \SoapFault
     */
    public function createMailForward($username, $password, $source, $destinations)
    {
        // $this->login($username,$password);
        $tmp = explode("@", $source);
        $nameEmail = $tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ? ou à legrain
            $error = true;
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($ndd->getUser()->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($user->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');


            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $log = new Log($user, 'Modification  de la redirection : ' . $source);

            $this->em->persist($log);
            $this->em->flush();
            return $gandiApi->createMailForward($connect, $domain, $nameEmail, $destinations);


        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $forwardAddress
     * @return \GandiBundle\Entity\Mailbox\ForwardReturn
     * @throws \SoapFault
     */
    public function getMailForward($username, $password, $forwardAddress)
    {
        $user = $this->login($username, $password);
        // $this->login($username,$password);
        $tmp = explode("@", $forwardAddress);
        //$loginEmail=$tmp[0];
        $nameEmail = $tmp[0];
        $domain = $tmp[1];

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


            return $gandiApi->getMailForward($connect, $domain, $nameEmail);

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @return mixed
     * @throws \SoapFault
     */
    public function countMailForward($username, $password, $domain)
    {
        $user = $this->login($username, $password);
        // $this->login($username,$password);


        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        // ndd est il dans la liste ?
        if ($ndd) {

            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ou  l'utilisateur qui demande est Super admin?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


            return $gandiApi->countMailForward($connect, $domain);

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @return mixed
     * @throws \SoapFault
     */
    public function listMailForward($username, $password, $domain)
    {
        $user = $this->login($username, $password);

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $list = $gandiApi->listMailForward($connect, $domain);
            return $list;
            /*
            $return =array();
            foreach($list as $item){
                $return[]=$item->login;
            }
            return $return;
            */
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $domain
     * @return \GandiBundle\Entity\Mailbox\MailboxListReturn[]
     * @throws \SoapFault
     */
    public function listMailbox($email, $password, $domain)
    {

        $user = $this->login($email, $password);

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $list = $gandiApi->listMailbox($connect, $domain);
            $return = array();
            $userRepository = $this->em->getRepository('AppBundle:User');
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            $respondeurRepository = $this->em->getRepository('AppBundle:ResponderEmail');
            //$listActive=false;
            foreach ($list as $elem) {
                //  $listActive=true;
                $emailApiExist = $userRepository->userExist($elem->login . '@' . $domain, $roleCompteEmail);
                // Ligne de répondeur existe ?
                $responder = $respondeurRepository->findOneByEmail($elem->login . '@' . $domain);
                $responderActive = $responder == null ? false : true;
                //$emailApiExist=false;
                $return[] = new \GandiBundle\Entity\Mailbox\MailboxListReturn($elem->login,
                    new \GandiBundle\Entity\Mailbox\MailboxQuota($elem->quota->granted, $elem->quota->used),
                    new \GandiBundle\Entity\Mailbox\MailboxResponder($responderActive),
                    $emailApiExist
                );
            }
            //  $ndd->setServiceEmail($listActive);
            //
            // $this->em->persist($ndd);
            // $this->em->flush();
            return $return;

            //  return $list;

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Ndd[]
     * @throws \SoapFault
     */
    public function listAllNdds($email, $password, $idUser)
    {
        $userApi = $this->login($email, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $user = $userRepository->find($idUser);

        if (!$user->getRoles()->contains($roleAgence)) throw new \SoapFault('access forbidden', 'Niveau d\'accès insuffisant');
        if ($user->getRoles()->contains($roleLegrain)) {
            $list = $nddRepository->findAll();
        } else {
            $list = $nddRepository->findByAgency($user->getAgency());
        }
        $return = array();
        foreach ($list as $item) {
            $u = $item->getUser();


            $ci = new City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
            $zi = new ZipCode($u->getZipcode()->getId(), $u->getZipcode()->getName());

            $a = $u->getAgency();
            $agCi = new City($a->getCity()->getId(), $a->getCity()->getName(), $a->getCity()->getCodeInsee());
            $agZi = new ZipCode($a->getZipcode()->getId(), $a->getZipcode()->getName());
            //$ag = new \AppBundle\Soap\Entity\Agency($a->getId(),$a->getName(),$a->getSiret(),$a->getAddress1(),$a->getAddress2(),$a->getAddress3(),$agCi,$agZi,$a->getPhone(),$a->getEmail(), $a->getWebsite(),null,null,null);
            $ag = $this->_getAgency($a);
            $usrndd = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(),
                $ci, $zi, $u->getPhone(), $u->getActive(), $ag, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName()
                , $u->getCodeClient(), $u->getNumTVA(), null);

            $p = $item->getProduct();
            $pp = $this->getProduct($email, $password, $p->getId(), $idUser);

            $product = new Product($p->getId(), $p->getName(), $p->getReference(), $p->getCodeLgr(), $p->getShortDescription(), $p->getLongDescription(), $p->getMinPeriod(), $pp->codeFacturationAgence, $pp->priceHT,
                $pp->minPriceHT, $pp->percentTax);
            $return[] = new \AppBundle\Soap\Entity\Ndd(

                $item->getId(), $item->getName(),
                null, null, null, null,
                null, null, $item->getExpirationDate()->getTimestamp(), null, null, null, null, null,
                null, null, null, $product, $item->getServices(), null, null, $usrndd

            );
        }
        return $return;


    }

    /**
     * @param string $email
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Ndd[]
     * @throws \SoapFault
     */
    public function listNdd($email, $password, $idUser)
    {
        $userApi = $this->login($email, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $user = $userRepository->find($idUser);


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }


        $ndds = $nddRepository->findBy(array('user' => $user->getId()), array('expirationDate' => 'ASC'));

        $return = array();
        foreach ($ndds as $ndd) {
            /*
             * id=null, $name=null, $status=null, $date_pending_delete_end=null, $date_updated=null, $date_delete=null,
                                $date_hold_end=null, $fqdn=null, $date_registry_end=null, $authinfo=null, $date_registry_creation=null, $date_renew_begin=null, $tld=null, $date_created=null,
                                $date_restore_end=null, $date_hold_begin=null,$nameservers=null,$product=null,$services=null
             */
            $p = $ndd->getProduct();
            $pp = $this->getProduct($email, $password, $p->getId(), $idUser);

            $product = new Product($p->getId(), $p->getName(), $p->getReference(), $p->getCodeLgr(), $p->getShortDescription(), $p->getLongDescription(), $p->getMinPeriod(), $pp->codeFacturationAgence, $pp->priceHT,
                $pp->minPriceHT, $pp->percentTax);
            $return[] = new \AppBundle\Soap\Entity\Ndd($ndd->getId(), $ndd->getName(),
                null, null, null, null,
                null, null, $ndd->getExpirationDate()->getTimestamp(), null, null, null, null, null,
                null, null, null, $product, $ndd->getServices()
            );
        }
        return $return;

    }

    /**
     * @param string $email
     * @param string $password
     * @param string $domain
     * @param array $nameServers
     * @return bool
     * @throws \SoapFault
     */
    public function setNameServer($email, $password, $domain, $nameServers)
    {
        $user = $this->login($email, $password);

        //$userRepository=$this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');


        $ndd = $nddRepository->findOneByName($domain);
        if (!$user->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($user->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if ($ndd) {

            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


            try {
                $gandiApi->setNameServers($connect, $domain, $nameServers);
                return true;
            } catch (\Exception $e) {
                foreach ($nameServers as $name) {
                    if (strpos($e->getMessage(), $name)) {
                        throw new \SoapFault('e', 'La valeur "' . $name . '" est incorrecte');
                    }
                }

            }
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $ndd
     * @return \AppBundle\Soap\Entity\Ndd
     * @throws \SoapFault
     */
    public function getNdd($email, $password, $ndd)
    {
        $user = $this->login($email, $password);

        //$userRepository=$this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');


        $ndd = $nddRepository->findOneByName($ndd);
        if (!$user->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($user->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if ($ndd) {

            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $infoDomain = $gandiApi->infosDomain($connect, $ndd->getName());

            $ndd->setExpirationDate(new \DateTime(date('Y-m-d', $infoDomain->dateRegistryEnd)));
            //if($infoDomain->services['gandimail'])
            //  $ndd->setServiceEmail(true);
            $ndd->setServices($infoDomain->services);
            $ndd->setIdGandi($infoDomain->id);

            $this->em->persist($ndd);
            $this->em->flush();
//           $infoDomain->authinfo

            $p = $this->getProduct($email, $password, $ndd->getProduct()->getId(), $ndd->getUser()->getId());

            $options = null;
            // Le ndd a t'il un pack mail ?
            $packMail = $ndd->getEmailGandiPackPro();

            if ($packMail) {
                $productRepository = $this->em->getRepository('AppBundle:Product');
                $p1 = $this->getProduct($email, $password, 1, $packMail->getUser()->getId());
                $p2 = $this->getProduct($email, $password, 2, $packMail->getUser()->getId());

                // On loade les actions

                $ppackMail =
                $options = array(
                    //$packMail->getSize()
                    $p1->name . ' (' . ($p1->minPriceHT ? $p1->minPriceHT : $p1->priceHT) . '€/mois)',
                    $p2->name . ' (' . ($p2->minPriceHT ? $p2->minPriceHT : $p2->priceHT) . '€/mois et par Go (' . $packMail->getSize() . 'Go))'
                );
            }

            $gc = $ndd->getContact();

            if ($gc && $gc->getUser())
                $contact = new ContactInfos($gc->getId(), $gc->getFakeEmail(), $gc->getIsDefault(), null, $gc->getCode());
            else
                $contact = null;
            return new \AppBundle\Soap\Entity\Ndd($ndd->getId(), $ndd->getName()
                , $infoDomain->status, $infoDomain->datePendingDeleteEnd, $infoDomain->dateUpdated, $infoDomain->dateDelete, $infoDomain->dateHoldEnd, $infoDomain->fqdn, $infoDomain->dateRegistryEnd,
                $infoDomain->authinfo, $infoDomain->dateRegistryCreation, $infoDomain->dateRenewBegin, $infoDomain->tld, $infoDomain->dateCreated,
                $infoDomain->dateRestoreEnd, $infoDomain->dateHoldBegin, $infoDomain->nameservers, $p, $ndd->getServices(), $options, $contact

            );
        } else {
            return new \AppBundle\Soap\Entity\Ndd(null, null);
        }


    }

    /**
     * @param string $username
     * @param string $password
     * @param string $emailAdress
     * @return bool
     * @throws \SoapFault
     */
    public function deleteMailbox($username, $password, $emailAdress)
    {

        $tmp = explode("@", $emailAdress);
        $loginEmail = $tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $password);

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            // La boite email possède elle un compte COMPTE_CLIENT_EMAIL, si oui, supprimer l'utilisateur associé
            $userRepository = $this->em->getRepository('AppBundle:User');
            $rolesRepository = $this->em->getRepository('AppBundle:Roles');
            $roleCompteEmail = $rolesRepository->findOneByName('ROLE_COMPTE_EMAIL');
            $userTmp = $userRepository->findOneByEmail($emailAdress);
            if ($userTmp && $userTmp->getRoles()->contains($roleCompteEmail)) {

                $this->em->remove($userTmp);

                $this->em->flush();
            }
            // supprimer la boite email
            $log = new Log($user, 'Suppression de la boite e-mail : ' . $emailAdress);

            $this->em->persist($log);
            $this->em->flush();

            return $gandiApi->deleteMailbox($connect, $domain, $loginEmail);


        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $emailAddress
     * @return \GandiBundle\Entity\Mailbox\MailboxReturn
     * @throws \SoapFault
     */
    public function getMailbox($username, $password, $emailAddress)
    {
//        $test = new EmailController();
//        return $test->getMailbox($username,$password,$emailAddress);

        $user = $this->login($username, $password);

        $tmp = explode("@", $emailAddress);
        $loginEmail = $tmp[0];
        $domain = $tmp[1];

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        //var_dump($ndd);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAddress == $username)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


            $res = $gandiApi->getMailbox($connect, $domain, $loginEmail);
            // throw new \SoapFault('aa',$loginEmail.' '.$domain);
            $userRepository = $this->em->getRepository('AppBundle:User');


            $active = 0;
            $message = null;
            $initDate = null;
            $endDate = null;

            $responderRepository = $this->em->getRepository('AppBundle:ResponderEmail');
            $responder = $responderRepository->findOneByEmail($res->login . '@' . $domain);

            if ($responder) {
                $active = $responder->getActiveGandi();
                $message = $responder->getMessage();
                $initDate = $responder->getInitDate();
                $endDate = $responder->getEndDate();
            }

            return new \GandiBundle\Entity\Mailbox\MailboxReturn($res->aliases, $res->fallback_email, $res->login,
                $res->quota,
                new \GandiBundle\Entity\Mailbox\MailboxResponder($active, $message, $initDate, $endDate),
                $userRepository->userExist($res->login . '@' . $domain, $roleCompteEmail),
                $userRepository->userDontExist($res->login . '@' . $domain, $roleCompteEmail)
            );

            //return $return;
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $emailAddress
     * @param array $aliases
     * @return bool
     * @throws \SoapFault
     */
    public function mailboxDeleteAliases($username, $password, $emailAddress, $aliases)
    {

        $user = $this->login($username, $password);

        $tmp = explode("@", $emailAddress);
        $loginEmail = $tmp[0];
        $domain = $tmp[1];


        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAddress == $username)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $return = $gandiApi->mailboxRemoveAliasAction($connect, $emailAddress, $aliases);

            $log = new Log($user, 'Suppression des alias (' . implode(', ', $aliases) . ') de la boite e-mail : ' . $emailAddress);

            $this->em->persist($log);
            $this->em->flush();
            return $return;
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @return \AppBundle\Soap\Entity\EmailInfos
     * @throws \SoapFault
     */
    public function mailboxInfos($username, $password, $domain)
    {

        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            $userDomain = explode('@', $username);

            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($domain == $userDomain[1])) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');

            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            // On regarde si le ndd possède ou non le pack mail pro
            $packMailProRepository = $this->em->getRepository('AppBundle:EmailGandiPackPro');

            $packMailPro = $packMailProRepository->findOneByNdd($ndd);

            $packMailProPossesses = $packMailPro ? true : false;
            $sizePackMailPro = $packMailPro ? $packMailPro->getSize() : 0;
            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $infos = $gandiApi->packMailInfo($connect, $domain);


            $return = new \AppBundle\Soap\Entity\EmailInfos($infos->date_created, $infos->date_end, $infos->domain, $ndd->getId(), $infos->forward_quota, $infos->mailbox_quota, $infos->status, $infos->storage_quota, $packMailProPossesses, $sizePackMailPro);


            // Si le compte est actif.
            return $return;
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param string $emailAddress
     * @param array $aliases
     * @return bool
     * @throws \SoapFault
     */
    public function mailboxAddAliases($username, $password, $emailAddress, $aliases)
    {
        $user = $this->login($username, $password);

        //  $aliases=property_exists($aliases,'item')?$aliases->item:array($aliases);
//        $aliases=array($alias);

        $tmp = explode("@", $emailAddress);
        $loginEmail = $tmp[0];
        $domain = $tmp[1];

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?

        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 2');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAddress == $username)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 3');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            try {
                $return = $gandiApi->mailboxAddAliasAction($connect, $emailAddress, $aliases);
            } catch (\SoapFault $e) {
                throw new \SoapFault($e->getFaultCode(), $e->getMessage());

            }

            $log = new Log($user, 'Ajout des alias (' . implode(', ', $aliases) . ') à la boite e-mail : ' . $emailAddress);

            $this->em->persist($log);
            $this->em->flush();
            return $return;
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable 1');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $emailAdress
     * @param string $passwordMailbox
     * @param int $quota
     * @param string $fallback_email
     * @return bool
     * @throws \SoapFault
     */
    public function updateMailbox($username, $password, $emailAdress, $passwordMailbox, $quota, $fallback_email)
    {
        // $this->login($username,$password);
        $tmp = explode("@", $emailAdress);
        //$loginEmail=$tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        $setQuota = true;
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAdress == $username)) {
                $error = false;
                $setQuota = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');

            if (!$setQuota) $setQuota = null;
            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $log = new Log($user, 'Mise à jour de la boite e-mail : ' . $emailAdress);

            $this->em->persist($log);
            $this->em->flush();
            return $gandiApi->updateMailbox($connect, $emailAdress, $passwordMailbox, (int)$quota, $fallback_email);

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param string $emailAdress
     * @param string $passwordMailbox
     * @param int $quota
     * @param string $fallback_email
     * @return bool
     * @throws \SoapFault
     */
    public function createMailbox($username, $password, $emailAdress, $passwordMailbox, $quota, $fallback_email)
    {

        $quota = $quota == null ? 0 : $quota;
        $fallback_email = $fallback_email == null ? '' : $fallback_email;
        $tmp = explode("@", $emailAdress);
        //$loginEmail=$tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $password);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $log = new Log($user, 'Création de la boite e-mail : ' . $emailAdress);
            try {
                $this->em->persist($log);
                $this->em->flush();

                return $gandiApi->createMailbox($connect, $emailAdress, $passwordMailbox, (int)$quota, $fallback_email);
            } catch (\Exception $e) {
                throw new \SoapFault('e', $e->getMessage());
            }
        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }


    /**
     * @param string $username
     * @param string $pwd
     * @param string $emailAddress
     * @param string $dateInit date format en (Y-m-d)
     * @param string $dateEnd date format en (Y-m-d)
     * @param string $message
     * @return bool
     * @throws \SoapFault
     */
    public function activateResponder($username, $pwd, $emailAddress, $dateInit, $dateEnd, $message)
    {
        $tmp = explode("@", $emailAddress);
        //$loginEmail=$tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $pwd);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAddress == $username)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');


            // Traitement ( appel gandi ws)
            //$gandiApi = new \GandiBundle\Controller\GandiController();

//            $usrGandi='hohloobeen1quaez7eis8eiBaiNgeita';
//            $passwordGandi='cooBeeNgeijaerie9aibae0ohxootee5';


            // $connect = new \GandiBundle\Entity\Connect($usrGandi,$passwordGandi);

            // Ajout (ou suppression et ajout) du répondeur.
            // On tente de Loadder le répondeur
            $responderEmailRepository = $this->em->getRepository('AppBundle:ResponderEmail');
            $responder = $responderEmailRepository->findOneByEmail($emailAddress);
            if (!$responder) {
//                $gandiApi->disableResponder($connect,$emailAddress,date('Y-m-d'));
                //  $this->em->remove($responder);
                // $this->em->flush();
                $responder = new ResponderEmail();
            }


            //   $gandiApi->activateResponder($connect,$emailAddress,$dateInit,$dateEnd,$message);
            $log = new Log($user, 'Activer le répondeur sur la boite e-mail : ' . $emailAddress);


            $responder->setEmail($emailAddress);
            $responder->setInitDate(new \DateTime($dateInit));
            $responder->setEndDate(new \DateTime($dateEnd));
            $responder->setMessage($message);
            $responder->setNdd($ndd);


            $this->em->persist($log);
            $this->em->persist($responder);
            $this->em->flush();

            return true;

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }


    /**
     * @param string $username
     * @param string $pwd
     * @param string $emailAddress
     * @param int $dateEnd
     * @return bool
     * @throws \SoapFault
     */
    public function disableResponder($username, $pwd, $emailAddress, $dateEnd)
    {
        $tmp = explode("@", $emailAddress);
        //$loginEmail=$tmp[0];
        $domain = $tmp[1];
        $user = $this->login($username, $pwd);
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);
        // ndd est il dans la liste ?
        if ($ndd) {
            // ndd a t'il un id Gandi ?
            if (!$ndd->getIdGandi()) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');

            // ndd appartient il à l'utilisateur ou à un fils de l'utilisateur ?
            $error = true;
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
            if ($user->getRoles()->contains($roleLegrain)) {
                $error = false;
            } elseif ($user->getRoles()->contains($roleCompteEmail) && ($emailAddress == $username)) {
                $error = false;
            } else {
                if ($ndd->getUser()->getId() == $user->getId()) {
                    $error = false;
                } else {
                    foreach ($user->getChildren() as $child) {
                        if ($child->getId() == $ndd->getUser()->getId()) {
                            $error = false;
                            break;
                        }

                    }
                }
            }
            if ($error) throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable ');


            // Traitement ( appel gandi ws)
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            $gandiApi->disableResponder($connect, $emailAddress, $dateEnd);
            $log = new Log($user, 'Désactiver le répondeur surla boite e-mail : ' . $emailAddress);

            $this->em->persist($log);
            $this->em->flush();
            return true;

        } else {
            throw new \SoapFault('DOMAIN_NOT_FOUND', 'Le domaine ' . $domain . ' est introuvable');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUserToBeUpdated
     * @param string $newEmail
     * @param string $newPassword
     * @param string $newName
     * @param string $newFirstname
     * @param string $newPhone
     * @param string $newAddress1
     * @param string $newAddress2
     * @param string $newAddress3
     * @param string $newCity
     * @param string $newZipcode
     * @param bool $active
     * @param int $newTiersPourTva
     * @param string $newCodeClient
     * @param string $newCellPhone
     * @param string $newWorkPhone
     * @param string $newCompanyName
     * @param string $newNumTva
     * @return bool
     * @throws \SoapFault
     */
    public function updateUser($username, $password, $idUserToBeUpdated, $newEmail, $newPassword = null, $newName, $newFirstname, $newPhone, $newAddress1, $newAddress2, $newAddress3, $newCity, $newZipcode, $active, $newTiersPourTva, $newCodeClient, $newCellPhone, $newWorkPhone, $newCompanyName, $newNumTva)
    {

        $user = $this->login($username, $password);

        $forbidden = true;
        // Si admin Ou
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        if ($user->getRoles()->contains($roleLegrain)) {
            $forbidden = false;
        } elseif ($user->getId() == $idUserToBeUpdated) {
            $forbidden = false;
        } else {
            foreach ($user->getChildren() as $child) {
                if ($child->getId() == $idUserToBeUpdated) {
                    $forbidden = false;
                    break;
                }
            }
        }
        if ($forbidden) throw new \SoapFault('FORBIDDEN', "Vous n'avez pas les autorisations necessaire pour modifier cet utilisateur");

        // load de l'utilisateur à modifier
        $userRepository = $this->em->getRepository('AppBundle:User');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $zp = $zpRepository->findOneByName($newZipcode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($newCity);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }


        $usrToBeUpdated = $userRepository->find($idUserToBeUpdated);
        $rolesRepository = $this->em->getRepository('AppBundle:Roles');
        $role_compte_email = $rolesRepository->findByName('ROLE_COMPTE_EMAIL');
        // On vérifie que le nouvel e-mail n'est pas dans la base

        if ($newEmail != $usrToBeUpdated->getEmail()) {
            $tmp = $userRepository->findOneByEmail($newEmail);
            if ($tmp) throw new \SoapFault('EMAIL_ALREADY_IN_DB', 'Cette adresse e-mail est déjà utilisé dans notre base de données');
        }
        if (!$usrToBeUpdated->getRoles()->contains($role_compte_email)) {
            $usrToBeUpdated->setEmail($newEmail);
        }
        $usrToBeUpdated->setName($newName);
        $usrToBeUpdated->setFirstname($newFirstname);
        $usrToBeUpdated->setPhone($newPhone);
        $usrToBeUpdated->setAddress1($newAddress1);
        $usrToBeUpdated->setAddress2($newAddress2);
        $usrToBeUpdated->setAddress3($newAddress3);
        $usrToBeUpdated->setCity($c);
        $usrToBeUpdated->setZipCode($zp);
        $usrToBeUpdated->setActive($active);

        $usrToBeUpdated->setCellPhone($newCellPhone);
        $usrToBeUpdated->setWorkPhone($newWorkPhone);
        if ($newCodeClient != null) $usrToBeUpdated->setCodeClient($newCodeClient);
        $usrToBeUpdated->setCompanyName($newCompanyName);
        $usrToBeUpdated->setNumTVA($newNumTva);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');
        $usrToBeUpdated->setTiersPourTVA($tiersPourTvaRepository->find($newTiersPourTva));


        if ($newPassword != null) {
            $passwordEncoded = $userRepository->encodePassword($usrToBeUpdated, $newPassword);
            $usrToBeUpdated->setPassword($passwordEncoded);
        }


        $this->em->persist($usrToBeUpdated);

        $log = new Log($user, 'Modifier l\'utilisateur : ' . $usrToBeUpdated->getFirstname() . ' ' . $usrToBeUpdated->getName() . ' (id : ' . $usrToBeUpdated->getId() . ')');
        $this->em->persist($log);
        $this->em->flush();
        return true;

    }

    /**
     * @param String $email
     * @param String $password
     * @return \AppBundle\Entity\User
     * @throws \SoapFault
     */
    private function login($email, $password)
    {
        if ($this->user == null) {
            // récupèration de l'objet user
            $userRepository = $this->em->getRepository('AppBundle:User');

            $user = $userRepository->findOneByEmail($email);

            if ($user == null) {
                throw new \SoapFault('BadCredentialsException', 'L\'utilisateur n\'est pas présent dans notre base de donnée.');
            }

            if (!$userRepository->checkLogIn($user, $password)) throw new \SoapFault('BadCredentialsException', 'L\'identifiant ou le mot de passe est incorrect');
            if (!$user->getActive()) throw new \SoapFault('DisabledException', 'Le compte est désactivé');
            // User set derniere date de connexion, + persist + flush
            $this->user = $user;
            $this->logger->info('On remplie $user depuis la bdd');

            return $user;
        } else {
            $this->logger->info('On retourne la variable de classe $this->user');
            return $this->user;
        }

    }

    private function checkAuthorisations(User $userMakeAction, User $userAVerifier)
    {
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $error = true;
        if ($userAVerifier == null) {
            if ($userMakeAction->getRoles()->contains($roleLegrain)) $error = false;
        } else {
            if ($userMakeAction->getRoles()->contains($roleLegrain)) $error = false;
            elseif ($userMakeAction->getRoles()->contains($roleAgence) && $userAVerifier->getAgency()->getId() == $userAVerifier->getAgency()->getId()) $error = false;
            elseif ($userMakeAction->getId() == $userAVerifier->getId()) $error = false;
        }
        if ($error) throw new \SoapFault('error', 'Accès interdit');
    }
// Other
    /**
     * @param string $email
     * @param string $password
     * @return \AppBundle\Soap\Entity\ZipCode[]
     * @throws \SoapFault
     */
    public function listZipCodes($email, $password)
    {
        $this->login($email, $password);
        $zipCodeRepository = $this->em->getRepository('AppBundle:ZipCode');
        $zipCodes = $zipCodeRepository->findAll();
        $return = array();
        foreach ($zipCodes as $z) {
            $return[] = new \AppBundle\Soap\Entity\ZipCode($z->getId(), $z->getName());
        }
        return $return;
    }

    /**
     * @param string $email
     * @param string $password
     * @return \AppBundle\Soap\Entity\City[]
     * @throws \SoapFault
     */
    public function listCities($email, $password)
    {
        $this->login($email, $password);
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $cities = $cityRepository->findAll();


        $return = array();
        foreach ($cities as $c) {
            $return[] = new \AppBundle\Soap\Entity\City($c->getId(), $c->getName(), $c->getCodeInsee());
        }
        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\Agency[]
     * @throws \SoapFault
     */
    public function listAgencies($username, $password)
    {
        $user = $this->login($username, $password);
        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agencies = $agencyRepository->findAll();


        $return = array();
        foreach ($agencies as $a) {
            $return[] = $this->_getAgency($a);
        }
        return $return;

    }

    private function _getAgency(Agency $a)
    {
        return new \AppBundle\Soap\Entity\Agency(
            $a->getId(),
            $a->getName(),
            $a->getSiret(),
            $a->getAddress1(),
            $a->getAddress2(),
            $a->getAddress3(),
            $a->getCity()!=null?new \AppBundle\Soap\Entity\City($a->getCity()->getId(), $a->getCity()->getName(), $a->getCity()->getCodeInsee()):null,
            $a->getZipCode()!=null?new \AppBundle\Soap\Entity\ZipCode($a->getZipCode()->getid(), $a->getZipCode()->getName()):null,
            $a->getPhone(),
            $a->getEmail(),
            $a->getWebsite(),
            $a->getFacturationBylegrainIsDefined()?$a->getFacturationBylegrain():null,
            $a->getInfosCheque(),
            $a->getInfosVirement(),
            $a->getUseTva(),
            $a->getDescriptionHtml()
        );
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idAgency
     * @return bool
     * @throws \SoapFault
     */
    public function deleteAgency($username, $password, $idAgency)
    {
        $user = $this->login($username, $password);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        if ($user->getAgency()->getId() == $idAgency) throw new \SoapFault('FORBIDDEN_ACTION', 'Impossible de supprimer sa propre agence');
        $agency = $agencyRepository->find($idAgency);
        //throw new \SoapFault('zz',$agency->getName());
        $log = new Log($user, 'Supprimer l\'agence : ' . $agency->getName());
        try {
            $this->em->remove($agency);
            $this->em->persist($log);
            $this->em->flush();
        } catch (\SoapFault $e) {
            throw new \SoapFault($e->getCode(), $e->getMessage());
        }


        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $siret
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $city
     * @param string $zipCode
     * @param string $phone
     * @param string $email
     * @param string $website
     * @return \AppBundle\Soap\Entity\Agency
     * @throws \SoapFault
     */
    public function createAgency($username, $password, $name, $siret, $address1, $address2, $address3, $city, $zipCode, $phone, $email, $website)
    {
        $user = $this->login($username, $password);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');
        // verif siret unique
        $tmpAgency = $agencyRepository->findOneBySiret($siret);
        if ($tmpAgency) throw new \SoapFault('ALREADY_EXIST', 'Le numéro de siret doit être unique');
        // verif zipCode
        $zp = $zpRepository->findOneByName($zipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        // SAve
        $agency = new Agency();
        $agency->setName($name);
        $agency->setSiret($siret);
        $agency->setAddress1($address1);
        $agency->setAddress2($address2);
        $agency->setAddress3($address3);
        $agency->setZipCode($zp);
        $agency->setCity($c);
        $agency->setPhone($phone);
        $agency->setEmail($email);
        $agency->setWebsite($website);
        $agency->setFacturationBylegrain(null);

        $this->em->persist($agency);

        $log = new Log($user, 'Creer l\'agence : ' . $agency->getName());

        $this->em->persist($log);
        // try {
        $this->em->flush();
        //}catch(\Exception $e) {
        //  throw new \SoapFault('zz', $e->getMessage());
        //}

        return $this->_getAgency($agency);

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idAgency
     * @return \AppBundle\Soap\Entity\Agency
     * @throws \SoapFault
     */
    public function getAgency($username, $password, $idAgency)
    {
        $user = $this->login($username, $password);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        $roleUtilisateurAgence = $roleReposytory->findOneByName('ROLE_UTILISATEUR_AGENCE');
        $roleAgence = $roleReposytory->findOneByName('ROLE_AGENCE');
        /*
        if(!$user->getRoles()->contains($roleLegrain)||!$user->getRoles()->contains($roleUtilisateurAgence)||!$user->getRoles()->contains($roleAgence))
            throw new \SoapFault('FORBIDDEN','Accès refusé');
        */
        if (!$user->getRoles()->contains($roleLegrain) && $idAgency != $user->getAgency()->getId())
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');


        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $agency = $agencyRepository->find($idAgency);
        return $this->_getAgency($agency);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $id
     * @param string $newName
     * @param string $newSiret
     * @param string $newAddress1
     * @param string $newAddress2
     * @param string $newAddress3
     * @param string $newCity
     * @param string $newZipCode
     * @param string $newPhone
     * @param string $newEmail
     * @param string $newWebsite
     * @param boolean $useTva
     * @return \AppBundle\Soap\Entity\Agency
     * @throws \SoapFault
     */
    public function updateAgency($username, $password, $id, $newName, $newSiret, $newAddress1, $newAddress2, $newAddress3, $newCity, $newZipCode, $newPhone, $newEmail, $newWebsite, $useTva)
    {
        $user = $this->login($username, $password);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');
        $agency = $agencyRepository->find($id);
        // Si le SIRET à changé
        if ($newSiret != $agency->getSiret()) {
            // verif siret unique
            $tmpAgency = $agencyRepository->findOneBySiret($newSiret);
            if ($tmpAgency) throw new \SoapFault('ALREADY_EXIST', 'Le numéro de siret doit être unique');
        }
        // verif zipCode
        $zp = $zpRepository->findOneByName($newZipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($newCity);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        // SAve

        $agency->setName($newName);
        $agency->setSiret($newSiret);
        $agency->setAddress1($newAddress1);
        $agency->setAddress2($newAddress2);
        $agency->setAddress3($newAddress3);
        $agency->setZipCode($zp);
        $agency->setCity($c);
        $agency->setPhone($newPhone);
        $agency->setEmail($newEmail);
        $agency->setWebsite($newWebsite);
        $agency->setUseTva($useTva);


        $log = new Log($user, 'Mise à jour de l\'agence : ' . $agency->getName());
        $this->em->persist($agency);
        $this->em->persist($log);
        $this->em->flush();
        return $this->_getAgency($agency);


    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\User[]
     * @throws \SoapFault
     */
    public function listUsers($username, $password)
    {
        $user = $this->login($username, $password);
//
        $userRepository = $this->em->getRepository('AppBundle:User');

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleReposytory->findOneByName('ROLE_AGENCE');
        if ($user->getRoles()->contains($roleLegrain)) {
            // On charge tous les utilisateurs (non comptes email)
            $users = $userRepository->findAllWithoutRoleCompteEmail();
        } elseif ($user->getRoles()->contains($roleAgence)) {
            // On charge tous les utilisateurs de l'agence (non comptes email)
            $users = $userRepository->findClientsAgencies($user->getAgency());
        } else {
            // On se charge soi même
            $users = array($user);
        }


//        $users = $userRepository->findUsersAgencies();
        $return = array();
        foreach ($users as $u) {
            $city =$u->getCity()==null?null: new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
            $zipcode = $u->getZipCode()==null?null:new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());
            //$agency = new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()), $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(),$u->getAgency()->getFacturationBylegrain(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement());
            $agency = $this->_getAgency($u->getAgency());
            $return[] = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(),($u->getTiersPourTVA()==null?null: new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName())));
        }
        return $return;


    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\User[]
     * @throws \SoapFault
     */
    public function listUsersAgencies($username, $password)
    {
        $user = $this->login($username, $password);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        $userRepository = $this->em->getRepository('AppBundle:User');

        $users = $userRepository->findUsersAgencies();
        $return = array();
        foreach ($users as $u) {
            $city =$u->getCity()==null?null: new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
            $zipcode =$u->getZipCode()==null?null: new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());
            //$agency = new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()), $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(),$u->getAgency()->getFacturationBylegrain(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement());
            $agency = $this->_getAgency($u->getAgency());
            $return[] = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(), ($u->getTiersPourTVA()==null?null:new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName())));
        }
        return $return;


    }

    /**

     * @param string $username
     * @param string $password
     * @param string $url
     * @return bool
     * @throws \SoapFault
     */
    public function setUrlApp($username, $password, $url)
    {
        $user = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');

        // Si gestionnaire
        if (!$user->getRoles()->contains($roleAgency)) throw new \SoapFault('ERROR', 'Accès refusé');
        if ($user->getAgency()->getUrlApp()!==null ) throw new \SoapFault('error', 'Vous avez déjà choisi un mode de facturation. Pour le changer, merci de vous adresser à Legrain');
        $user->getAgency()->setUrlApp($url);
        $this->em->persist($user);
        $this->em->flush();
        return true;
    }
    /**
     * Choix de la facturation par legrain. mode est à true le cas écheant
     * @param string $username
     * @param string $password
     * @param bool $mode
     * @return bool
     * @throws \SoapFault
     */
    public function choseFacturationMode($username, $password, $mode)
    {
        $user = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');

        // Si gestionnaire
        if (!$user->getRoles()->contains($roleAgency)) throw new \SoapFault('ERROR', 'Accès refusé');
        if ($user->getAgency()->getFacturationBylegrainIsDefined() ) throw new \SoapFault('error', 'Vous avez déjà choisi un mode de facturation. Pour le changer, merci de vous adresser à Legrain');
        $mode = $mode==true?1:0;

        try {

            $user->getAgency()->setFacturationBylegrain($mode);
            $user->getAgency()->setFacturationBylegrainIsDefined(true);


            // On cherche la grille par défaut du membre si facturationBy legrain =false. Si elle n'existe pas, on en créé une
            if (!$mode) {
                $plByDefault = new PriceList();
                $plByDefault->setIsDefault(true);
                $plByDefault->setName('defaut');
                $plByDefault->setParentAgency($user->getAgency());
                $this->em->persist($plByDefault);
            }

            $this->em->persist($user);
            $this->em->flush();

            return true;
        }catch (\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }
    /**
     * Complements infos user
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $firstname
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $zipCode
     * @param string $city
     * @param string $phone
     * @param string $cellphone
     * @param string $workphone
     * @param string $email
     * @param string $companyName
     * @param string $numTva
     * @param string $idTiersPourTva
     * @return bool
     * @throws \SoapFault
     */
    public function complementInfosGestionnaire($username, $password, $name,$firstname=null,$address1,$address2=null,$address3=null,$zipCode,$city,$phone=null,$cellphone=null,$workphone=null,$email,$companyName=null,$numTva=null,$idTiersPourTva){

        $user = $this->login($username, $password);

        $forbidden = true;
        // Si admin Ou
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');
        if ($user->getRoles()->contains($roleAgency)) $forbidden = false;

        if ($forbidden) throw new \SoapFault('FORBIDDEN', "Vous n'avez pas les autorisations necessaire pour modifier cet utilisateur");

        // load de l'utilisateur à modifier
        $userRepository = $this->em->getRepository('AppBundle:User');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $zp = $zpRepository->findOneByName($zipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }


        // On vérifie que le nouvel e-mail n'est pas dans la base

        if ($email != $user->getEmail()) {
            $tmp = $userRepository->findOneByEmail($email);
            if ($tmp) throw new \SoapFault('EMAIL_ALREADY_IN_DB', 'Cette adresse e-mail est déjà utilisé dans notre base de données');
        }

        $user->setEmail($email);

        $user->setName($name);
        $user->setFirstname($firstname);
        $user->setPhone($phone);
        $user->setAddress1($address1);
        $user->setAddress2($address2);
        $user->setAddress3($address3);
        $user->setCity($c);
        $user->setZipCode($zp);

        $user->setCellPhone($cellphone);
        $user->setWorkPhone($workphone);
        $user->setCompanyName($companyName);
        $user->setNumTVA($numTva);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');
        $user->setTiersPourTVA($tiersPourTvaRepository->find($idTiersPourTva));




        $this->em->persist($user);

        $log = new Log($user, 'Complement d\'infos pour l\'utilisateur : ' . $user->getFirstname() . ' ' . $user->getName() . ' (id : ' . $user->getId() . ')');
        $this->em->persist($log);
        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $descriptionHtml
     * @return bool
     * @throws \SoapFault
     *
     */
    public function setDescriptionAgency($username, $password, $descriptionHtml){

        $user = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');

        // Si gestionnaire
        if (!$user->getRoles()->contains($roleAgency)) throw new \SoapFault('ERROR', 'Accès refusé');

        $ag = $user->getAgency();
        $ag->setDescriptionHtml($descriptionHtml);
        $this->em->persist($ag);
        $this->em->flush();
        return true;

    }
    /**
     * Complements infos agence
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $siret
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $zipCode
     * @param string $city
     * @param string $phone
     * @param string $email
     * @param string $website
     * @return bool
     * @throws \SoapFault
     */
    public function complementInfosAgency($username, $password, $name,$siret,$address1,$address2=null,$address3=null,$zipCode,$city,$phone=null,$email,$website=null){
        $user = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleAgency = $roleRepository->findOneByName('ROLE_AGENCE');

        // Si gestionnaire
        if (!$user->getRoles()->contains($roleAgency)) throw new \SoapFault('ERROR', 'Accès refusé');

        $ag = $user->getAgency();
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        // Si le SIRET à changé
        // verif siret unique
        $tmpAgency = $agencyRepository->findOneBySiret($siret);
        if ($tmpAgency)  {

            if($tmpAgency->getId()!=$ag->getId())throw new \SoapFault('ALREADY_EXIST', 'Le numéro de siret doit être unique');
        }

        // verif zipCode
        $zp = $zpRepository->findOneByName($zipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }



        $ag->setName($name);
        $ag->setSiret($siret);
        $ag->setAddress1($address1);
        $ag->setAddress2($address2);
        $ag->setAddress3($address3);
        $ag->setPhone($phone);
        $ag->setEmail($email);
        $ag->setWebsite($website);
        $ag->setZipCode($zp);
        $ag->setCity($c);


        $this->em->persist($user);
        $this->em->flush();
        return true;

    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idAgency
     * @return \AppBundle\Soap\Entity\User[]
     * @throws \SoapFault
     */
    public function listClientsAgencies($username, $password, $idAgency)
    {
        $user = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        if (!$user->getRoles()->contains($roleLegrain) && $idAgency != $user->getAgency()->getId())
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        if (!$user->getRoles()->contains($roleAgence))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');

        $agency = $agencyRepository->find($idAgency);
        if ($user->getRoles()->contains($roleLegrain) && $user->getAgency()->getId() == $idAgency) {
            $users = $userRepository->findClientsAgenciesAndOtherGestionnary($agency);
        } else {
            $users = $userRepository->findClientsAgencies($agency);
        }

        $return = array();
        foreach ($users as $u) {
            $city =$u->getCity()==null?null: new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
            $zipcode =$u->getZipCode()==null?null: new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());
            //$agency = new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()), $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(),$u->getAgency()->getFacturationBylegrain(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement());
            $agency = $this->_getAgency($u->getAgency());
            $return[] = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(),( $u->getTiersPourTVA()==null?null:new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName())));
        }
        return $return;
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $agencyName
     * @return bool
     * @throws \SoapFault
     */
    public function registerAgencyMinimalistInfos($email,$password,$agencyName){
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $agencyReposytory = $this->em->getRepository('AppBundle:Agency');
        $roleAgence = $roleReposytory->findOneByName('ROLE_AGENCE');
        $userTmp = $userRepository->findOneByEmail($email);
        if ($userTmp) throw new \SoapFault('EMAIL_ALDRADY_USED', 'Un utilisateur possède déjà cette adresse e-mail');
        try {
            $user = new User();
            $user->setEmail($email);
            $user->setActive(true);
            $user->setRegistrationDate(new \DateTime());
            $user->setPassword($userRepository->encodePassword($user, $password));
            $user->addRole($roleAgence);


            $agency = new Agency();
            $agency->setName($agencyName);
            $agency->setEmail($email);
            $this->em->persist($agency);
            $user->setAgency($agency);
            $this->em->persist($user);
            $this->em->flush();



            // Email au gestionnaire pour signaler qu'une agence s'est inscrite
            $message = \Swift_Message::newInstance()
                ->setSubject('Inscription d\'une nouvelle agence sur GWI-HOSTING')
                ->setFrom($this->email_app)
                ->setTo($this->email_notification_inscription)
                ->setBody($this->twig->render('Email/inscriptionAgencePourLegrain.email.html.twig', array('agency' => $agency)));
            $this->mailer->send($message);


            return true;
        }catch(\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }
    /**
     * @param string $username
     * @param string $apiPassword
     * @param string $name
     * @param string $firstname
     * @param string $email
     * @param string $password
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $city
     * @param string $zipCode
     * @param int $idAgency
     * @param string $phone
     * @param bool $active
     * @param int $tiersPourTva
     * @param string $codeClient
     * @param string $cellPhone
     * @param string $workPhone
     * @param string $companyName
     * @param string $numTva
     * @return bool
     * @throws \SoapFault
     */
    public function createUserAgency($username, $apiPassword, $name, $firstname, $email, $password, $address1, $address2, $address3, $city, $zipCode, $idAgency, $phone, $active, $tiersPourTva, $codeClient, $cellPhone, $workPhone, $companyName, $numTva)
    {
        $user = $this->login($username, $apiPassword);

        $roleReposytory = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleReposytory->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $roleUserAgency = $roleReposytory->findOneByName('ROLE_AGENCE');

        $agency = $agencyRepository->find($idAgency);
        $userTmp = $userRepository->findOneByAgency($agency);
        // Si l'agence possède déjà un utilisateur de type agence. exeption
        if ($userTmp) throw new \SoapFault('AGENCY_ALREADY_USED', 'Cette agence possède déjà un utilisateur agence');
        $userTmp = $userRepository->findOneByEmail($email);
        if ($userTmp) throw new \SoapFault('EMAIL_ALDRADY_USED', 'Un utilisateur possède déjà cette adresse e-mail');


        // verif zipCode
        $zp = $zpRepository->findOneByName($zipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $date = new \DateTime();
        $user2 = new User();
        $user2->setEmail($email);
        $user2->setName($name);
        $user2->setFirstname($firstname);
        $user2->setAddress1($address1);
        $user2->setAddress2($address2);
        $user2->setAddress3($address3);
        $user2->setCity($c);
        $user2->setZipcode($zp);
        $user2->setActive($active);
        $user2->setRegistrationDate($date);

        $user2->setPassword($userRepository->encodePassword($user2, $password));
        $user2->setAgency($agency);
        $user2->setPhone($phone);

        $user2->setCellPhone($cellPhone);
        $user2->setWorkPhone($workPhone);
        $user2->setCodeClient($codeClient);
        $user2->setCompanyName($companyName);
        $user2->setNumTVA($numTva);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');
        $user2->setTiersPourTVA($tiersPourTvaRepository->find($tiersPourTva));


        $user2->addRole($roleUserAgency);

        $this->em->persist($user2);

        $log = new Log($user, 'Créér le gestionnaire : ' . $user2->getFirstName() . ' ' . $user2->getName());
        $this->em->persist($log);

        $this->em->flush();

        return true;
    }

    /**
     * @param string $urlApp
     * @param string $name
     * @param string $firstname
     * @param string $email
     * @param string $password
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $city
     * @param string $zipCode
     * @param string $phone
     * @param string $cellPhone
     * @param string $workPhone
     * @param string $companyName
     * @param string $numTva
     * @return \AppBundle\Soap\Security\UserSecurity
     * @throws \SoapFault
     */
    public function registerClientAgency($urlApp, $name, $firstname, $email, $password, $address1, $address2, $address3, $city, $zipCode, $phone, $cellPhone, $workPhone, $companyName, $numTva)
    {


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $zcRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $roleClientAgency = $roleRepository->findOneByName('ROLE_UTILISATEUR_AGENCE');


        $active = 1;
        // Récupération de l'agence par rapport à l'url de l'application ( si introuvable, on prend legrain)
        $agency = $this->_getAgencyPerUrlApp($urlApp);
        // $parent = gestionnaire de l'agence
        $parent = $userRepository->findParentByAgency($agency);

        // verif zipCode
        $zc = $zcRepository->findOneByName($zipCode);
        if (!$zc) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zc->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zc->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zc)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zc->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zc->getName() . ' : ' . $listCitiesAllowed . ')');
        }

        $date = new \DateTime();
        $user2 = new User();
        $user2->setEmail($email);
        $user2->setName($name);
        $user2->setFirstname($firstname);
        $user2->setAddress1($address1);
        $user2->setAddress2($address2);
        $user2->setAddress3($address3);
        $user2->setCity($c);
        $user2->setZipcode($zc);
        $user2->setActive($active);
        $user2->setRegistrationDate($date);

        $user2->setPassword($userRepository->encodePassword($user2, $password));
        $user2->setAgency($agency);
        $user2->setPhone($phone);

        $user2->setCellPhone($cellPhone);
        $user2->setWorkPhone($workPhone);
        $user2->setCompanyName($companyName);
        $user2->setNumTVA($numTva);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');
        // france
        $user2->setTiersPourTVA($tiersPourTvaRepository->find(1));


        $user2->addRole($roleClientAgency);

        $user2->setParent($parent);

        $this->em->persist($user2);

//        $log = new Log($user2, 'Créér le client : ' . $user2->getFirstName() . ' ' . $user2->getName());
//        $this->em->persist($log);

        $roles = array();
        foreach ($user2->getRoles() as $r) {
            $roles[] = $r->getName();
        }

        $agCity = new \AppBundle\Soap\Entity\City($agency->getCity()->getId(), $agency->getCity()->getName(), $agency->getCity()->getCodeInsee());
        $agZipCode = new \AppBundle\Soap\Entity\ZipCode($agency->getZipCode()->getId(), $agency->getZipCode()->getName());


        $soapUser = new \AppBundle\Soap\Security\UserSecurity(
            $user2->getId(),
            $user2->getName(),
            $user2->getFirstname(),
            $user2->getEmail(),
            $user2->getPassword(),

            $user2->getRegistrationDate()->getTimestamp(),
            $user2->getAddress1(),
            $user2->getAddress2(),
            $user2->getAddress3(),
            new \AppBundle\Soap\Entity\City($c->getId(), $c->getName(), $c->getCodeInsee()),
            new \AppBundle\Soap\Entity\ZipCode($zc->getId(), $zc->getName()),
            $user2->getPhone(),
            $user2->getCellPhone(),
            $user2->getWorkPhone(),
            $user2->getActive(),
            //new \AppBundle\Soap\Entity\Agency($agency->getId(),$agency->getName(),$agency->getSiret(),$agency->getAddress1(),$agency->getAddress2(),$agency->getAddress3(),$agCity,$agZipCode,$agency->getPhone(),$agency->getEmail(),$agency->getWebsite(),$agency->getFacturationBylegrain(),$agency->getInfosCheque(),$agency->getInfosVirement()),
            $this->_getAgency($agency),
            $roles,
            $user2->getCodeClient(),
            $user2->getCompanyName(),
            $user2->getCompanyName(),
            new TiersPourTVA($user2->getTiersPourTVA()->getId(), $user2->getTiersPourTVA()->getName())


        );
        $this->em->flush();
        return $soapUser;


    }

    /**
     * @param string $username
     * @param string $apiPassword
     * @param string $name
     * @param string $firstname
     * @param string $email
     * @param string $password
     * @param string $address1
     * @param string $address2
     * @param string $address3
     * @param string $city
     * @param string $zipCode
     * @param int $idAgency
     * @param string $phone
     * @param bool $active
     * @param int $tiersPourTva
     * @param string $codeClient
     * @param string $cellPhone
     * @param string $workPhone
     * @param string $companyName
     * @param string $numTva
     * @return int
     * @throws \SoapFault
     */
    public function createClientAgency($username, $apiPassword, $name, $firstname, $email, $password, $address1, $address2, $address3, $city, $zipCode, $idAgency, $phone, $active, $tiersPourTva, $codeClient, $cellPhone, $workPhone, $companyName, $numTva)
    {
        $user = $this->login($username, $apiPassword);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        if (!$user->getRoles()->contains($roleLegrain) && $user->getAgency()->getId() != $idAgency)
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        if (!$user->getRoles()->contains($roleAgence))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');


        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $roleClientAgency = $roleRepository->findOneByName('ROLE_UTILISATEUR_AGENCE');

        $agency = $agencyRepository->find($idAgency);

        $userTmp = $userRepository->findOneByEmail($email);
        if ($userTmp) throw new \SoapFault('EMAIL_ALDRADY_USED', 'Un utilisateur possède déjà cette adresse e-mail');

        // On récupère le pere :
        $parent = $userRepository->findParentByAgency($agency);


        // verif zipCode
        $zp = $zpRepository->findOneByName($zipCode);
        if (!$zp) throw new \SoapFault('UNKHNOW_FIELD', 'Ce code postal n\'est pas présent dans la base de donnée');
        // verif ville dans bdd

        $lc = $cityRepository->findByName($city);
        if (!$lc) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('UNKHNOW_FIELD', 'Cette ville n\'est pas présente dans la base de donnée (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }
        $fault = true;
        foreach ($lc as $c) {
            if ($c->getZipCodes()->contains($zp)) {
                $fault = false;
                break;
            }

        }
        // vérif le zipCode contient la ville
        if ($fault) {
            // récuperation des villes pour le code postal
            $listCitiesAllowed = '';
            $citiesAllowed = $zp->getCities();
            foreach ($citiesAllowed as $cityAllowed) {
                $listCitiesAllowed .= $cityAllowed->getName() . ', ';
            }
            $listCitiesAllowed = substr($listCitiesAllowed, 0, -2);
            throw new \SoapFault('BAD_PARAMETERS', 'Cette ville et ce code postal ne sont pas liés (Ville(s) associée(s) au code postal ' . $zp->getName() . ' : ' . $listCitiesAllowed . ')');
        }


        $date = new \DateTime();
        $user2 = new User();
        $user2->setEmail($email);
        $user2->setName($name);
        $user2->setFirstname($firstname);
        $user2->setAddress1($address1);
        $user2->setAddress2($address2);
        $user2->setAddress3($address3);
        $user2->setCity($c);
        $user2->setZipcode($zp);
        $user2->setActive($active);
        $user2->setRegistrationDate($date);

        $user2->setPassword($userRepository->encodePassword($user2, $password));
        $user2->setAgency($agency);
        $user2->setPhone($phone);

        $user2->setCellPhone($cellPhone);
        $user2->setWorkPhone($workPhone);
        $user2->setCodeClient($codeClient);
        $user2->setCompanyName($companyName);
        $user2->setNumTVA($numTva);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');
        $user2->setTiersPourTVA($tiersPourTvaRepository->find($tiersPourTva));


        $user2->addRole($roleClientAgency);

        $user2->setParent($parent);

        $this->em->persist($user2);
        $log = new Log($user, 'Créér le client : ' . $user2->getFirstName() . ' ' . $user2->getName());
        $this->em->persist($log);

        $this->em->flush();

        return $user2->getId();
    }

    /**
     * @param string $username
     * @param string $apiPassword
     * @param string $email
     * @return bool
     * @throws \SoapFault
     */
    public function createClientCompteEmail($username, $apiPassword, $email)
    {
        $user = $this->login($username, $apiPassword);

        // récuperation du domaine.
        $tmp = explode('@', $email);
        $ndd = $tmp[1];
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $objNdd = $nddRepository->findOneByName($ndd);
        $agency = $objNdd->getUser()->getAgency();

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $accessGranted = false;
        if ($user->getRoles()->contains($roleLegrain) && $user->getAgency()->getId() == $agency->getId())
            $accessGranted = true;
        elseif ($user->getRoles()->contains($roleAgence))
            $accessGranted = true;
        elseif ($user->getId() == $objNdd->getUser()->getId()) {
            $accessGranted = true;

        }

        if (!$accessGranted) throw new \SoapFault('FORBIDDEN', 'Accès refusé');


        $userRepository = $this->em->getRepository('AppBundle:User');
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $zpRepository = $this->em->getRepository('AppBundle:ZipCode');
        $cityRepository = $this->em->getRepository('AppBundle:City');

        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');

        //  $agency = $agencyRepository->find($idAgency);

        $userTmp = $userRepository->findOneByEmail($email);

        if ($userTmp) throw new \SoapFault('EMAIL_ALDRADY_USED', 'Un utilisateur possède déjà cette adresse e-mail');
        // On récupère le pere :
        $parent = $userRepository->findParentByAgency($agency);


        $length = 5;
        $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);


        $date = new \DateTime();
        $user2 = new User();
        $user2->setEmail($email);
        $user2->setName('');
        $user2->setFirstname('');
        $user2->setAddress1('');
        $user2->setAddress2('');
        $user2->setAddress3('');
        $user2->setCity($agency->getCity());
        $user2->setZipcode($agency->getZipCode());
        $user2->setActive(true);
        $user2->setRegistrationDate($date);

        $user2->setPassword($userRepository->encodePassword($user2, $password));
        $user2->setAgency($agency);
        $user2->setPhone('');


        $user2->addRole($roleCompteEmail);

        $user2->setParent($parent);

        $this->em->persist($user2);

        $log = new Log($user, 'Créér le client de type e-mail : ' . $user2->getFirstName() . ' ' . $user2->getName());
        $this->em->persist($log);

        $this->em->flush();
        // Email
        $message = \Swift_Message::newInstance()
            ->setSubject('Activation du compte de gestion de votre boite e-mail')
            ->setFrom($this->email_app)
            ->setTo($email)
            ->setBody($this->twig->render('Email/createNewUserCompteEmail.email.html.twig', array('email' => $email, 'password' => $password, 'agencyName' => $agency->getName())));
        $this->mailer->send($message);

        return true;
    }

    /**
     * @param string $username
     * @param string $apiPassword
     * @param int $iduser
     * @return bool
     * @throws \SoapFault
     */
    public function deleteUser($username, $apiPassword, $iduser)
    {
        $user = $this->login($username, $apiPassword);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $userToBeDelete = $userRepository->find($iduser);

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain) && $user->getAgency()->getId() != $userToBeDelete->getAgency()->getId())
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        if ($user->getId() == $iduser) throw new \SoapFault('FORBIDDEN', 'impossible de supprimer son propre compte refusé');


        $log = new Log($user, 'Supprimer l\'utilisateur : ' . $userToBeDelete->getFirstName() . ' ' . $userToBeDelete->getName());
        $this->em->persist($log);

        $this->em->remove($userToBeDelete);

        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $apiPassword
     * @param string $email
     * @return bool
     * @throws \SoapFault
     */
    public function deleteClientCompteEmail($username, $apiPassword, $email)
    {
        $user = $this->login($username, $apiPassword);

        // récuperation du domaine.
        $tmp = explode('@', $email);
        $ndd = $tmp[1];
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $objNdd = $nddRepository->findOneByName($ndd);
        $agency = $objNdd->getUser()->getAgency();

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');
        $accessGranted = false;
        if ($user->getRoles()->contains($roleLegrain) && $user->getAgency()->getId() == $agency->getId())
            $accessGranted = true;
        elseif ($user->getRoles()->contains($roleAgence))
            $accessGranted = true;
        elseif ($user->getId() == $objNdd->getUser()->getId()) {
            $accessGranted = true;

        }

        if (!$accessGranted) throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        $userRepository = $this->em->getRepository('AppBundle:User');


        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        // On loade l'utilisateur a supprimer
        $userToBeDelete = $userRepository->findOneByEmail($email);
        if (!$userToBeDelete->getRoles()->contains($roleCompteEmail)) throw new \SoapFault('error', 'Impossible de supprimer cet utilisateur car il n\'est pas un compte email');
        $log = new Log($user, 'Supprimer l\'utilisateur de type compte e-mail : ' . $userToBeDelete->getFirstName() . ' ' . $userToBeDelete->getName());
        $this->em->persist($log);
        $this->em->remove($userToBeDelete);
        $this->em->flush();


        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\User[]
     * @throws \SoapFault
     */
    public function listAllUsers($username, $password)
    {
        $user = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleCompteEmail = $roleRepository->findOneByName('ROLE_COMPTE_EMAIL');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        $userRepository = $this->em->getRepository('AppBundle:User');
        $users = $userRepository->findAll();

        $return = array();
        foreach ($users as $u) {
            if (!$u->getRoles()->contains($roleCompteEmail)) {
                $city = $u->getCity()==null?null : new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
                $zipcode = $u->getZipCode()==null?null:new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());

                $agency = $this->_getAgency($u->getAgency());
                $return[] = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(), ($u->getTiersPourTVA()==null?null:new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName())));
            }
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\User
     * @throws \SoapFault
     */
    public function getCustomer($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $userRepository = $this->em->getRepository('AppBundle:User');


        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $user = $userRepository->find($idUser);
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        $u = $userRepository->find($idUser);
        $city =$u->getCity()==null?null: new \AppBundle\Soap\Entity\City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
        $zipcode = $u->getZipCode()==null?null:new \AppBundle\Soap\Entity\ZipCode($u->getZipCode()->getId(), $u->getZipCode()->getName());
        //$agency = new \AppBundle\Soap\Entity\Agency($u->getAgency()->getId(), $u->getAgency()->getName(), $u->getAgency()->getSiret(), $u->getAgency()->getAddress1(), $u->getAgency()->getAddress2(), $u->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($u->getAgency()->getCity()->getId(), $u->getAgency()->getCity()->getName(), $u->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($u->getAgency()->getZipCode()->getId(), $u->getAgency()->getZipCode()->getName()), $u->getAgency()->getPhone(), $u->getAgency()->getEmail(), $u->getAgency()->getWebsite(),$u->getAgency()->getFacturationBylegrain(),$u->getAgency()->getInfosCheque(),$u->getAgency()->getInfosVirement());
        $agency = $this->_getAgency($u->getAgency());
        return new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(), $city, $zipcode, $u->getPhone(), $u->getActive(), $agency, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName(), $u->getCodeClient(), $u->getNumTVA(),($u->getTiersPourTVA()==null?null: new TiersPourTVA($u->getTiersPourTVA()->getId(), $u->getTiersPourTVA()->getName())));

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param string $ndd
     * @return bool
     * @throws \SoapFault
     */
    public function createDomainAndSetUser($username, $password, $idUser, $ndd)
    {
        $user = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        $userRepository = $this->em->getRepository('AppBundle:User');

        $userAction = $userRepository->find($idUser);
        // On regarde si le ndd est déjà sauvé dans la base.
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $testNdd = $nddRepository->findOneByName($ndd);
        if ($testNdd != null) throw new \SoapFault('server', 'Ce nom de domaine est déjà sauvegardé (client : ' . $testNdd->getUser()->getName() . ')');
        // On vérifie que le ndd est bien présent chez Gandi

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        try {
            $infoDomain = $gandiApi->infosDomain($connect, $ndd);
        } catch (\Exception $e) {
            throw new \SoapFault('server', $e->getMessage());
        }

        $oNdd = new Ndd();
        $oNdd->setName($ndd);
        $oNdd->setIdGandi('445');
        // Fixe une date par défaut. Afin de supprimer le bug si ma date n'est pas récupérée de chez Gandi
        $oNdd->setExpirationDate(new \DateTime());
        $oNdd->setUser($userAction);
        // Ajouter produit correspondant au ndd

        $arrayNdd = explode('.', $ndd);
        $ext = $arrayNdd[count($arrayNdd) - 1];


        $productRepository = $this->em->getRepository('AppBundle:Product');
        $product = $productRepository->findOneByReference('renewndd' . $ext);

        if ($product) {
            $oNdd->setProduct($product);
        }

        $this->em->persist($oNdd);
        $log = new Log($user, 'Liaison entre l\'utilisateur ' . $userAction->getFirstName() . ' ' . $userAction->getName() . ' et le ndd : ' . $oNdd->getName());
        $this->em->persist($log);
        $this->em->flush();


        $this->getNdd($username, $password, $ndd);
        return true;

    }

    /**
     * @param string $username
     * @return bool
     * @throws \SoapFault
     */
    public function getMyPassword($username)
    {

        $userRepository = $this->em->getRepository('AppBundle:User');
        if (null == $user = $userRepository->findOneByEmail($username)) throw new \SoapFault('BadCredentialsException', 'L\'utilisateur n\'est pas présent dans notre base de donnée.');
        if (!$user->getActive()) throw new \SoapFault('DisabledException', 'Le compte est désactivé');
        // Génération d'un mot de passe
        $length = 7;
        $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);

        $user->setPassword($userRepository->encodePassword($user, $password));
        // Envoi du mdp par email
        $message = \Swift_Message::newInstance()
            ->setSubject('Renouvellement de mon mot de passe')
            ->setFrom($this->email_app)
            ->setTo($user->getEmail())
            ->setBody($this->twig->render('Email/newPassword.email.html.twig', array('email' => $user->getEmail(), 'password' => $password, 'agencyName' => $user->getAgency()->getName())));
        $this->mailer->send($message);
        $this->em->persist($user);
        //$log = new Log($user,'Nouveau mot de passe : '.$password);
        //$this->em->persist($log);
        $this->em->flush();
        return true;
    }


    /**
     * @param string $username
     * @param string $password
     * @return \GandiBundle\Entity\GInstance[]
     * @throws \SoapFault
     */
    public function listAllGInstances($username, $password)
    {
        $user = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        if (!$user->getRoles()->contains($roleLegrain))
            throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        $return = array();

        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $listInstances = $gandiApi->listInstances($connect);
        foreach ($listInstances as $li) {
            // On regarde si l'instance est déjà sauvé. Si c'est le cas, on ne l'ajoute pas à la liste
            if ($instanceRepository->findOneByIdGandi($li->id_g) == null)
                $return[] = new \GandiBundle\Entity\GInstance($li->name, $li->date_end, $li->id_g);
        }
        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @param int $idinstance
     * @param string $typeProduct
     * @return bool
     * @throws \SoapFault
     */
    public function createInstanceAndSetUser($username, $password, $iduser, $idinstance, $typeProduct)
    {
        try {

            $user = $this->login($username, $password);
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
            if (!$user->getRoles()->contains($roleLegrain))
                throw new \SoapFault('FORBIDDEN', 'Accès refusé');

            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            // On récupere l'instance chez Gandi
            $gi = $gandiApi->getInstance($connect, $idinstance);
            $userRepository = $this->em->getRepository('AppBundle:User');
            $usrSave = $userRepository->find($iduser);


            $productRepository = $this->em->getRepository('AppBundle:Product');
            switch ($typeProduct) {
                case 'instance':
                    $product = $productRepository->findOneByReference('instance');
                    $productRenew = $productRepository->findOneByReference('renewinstance');
                    $nbreVhosts = 1;
                    break;
                case '5.5':
                    $product = $productRepository->findOneByReference('instance5.5');
                    $productRenew = $productRepository->findOneByReference('renewinstance5.5');
                    $nbreVhosts = 5;
                    break;

                case '10':
                    $product = $productRepository->findOneByReference('instance10');
                    $productRenew = $productRepository->findOneByReference('renewinstance10');
                    $nbreVhosts = 1;
                    break;

                case '15':
                    $product = $productRepository->findOneByReference('instance15');
                    $productRenew = $productRepository->findOneByReference('renewinstance15');
                    $nbreVhosts = 100;
                    break;
                case 'immo':
                    $product = $productRepository->findOneByReference('instanceimmo');
                    $productRenew = $productRepository->findOneByReference('renewinstanceimmo');
                    $nbreVhosts = 1;
                    break;
                case 'immoe':
                    $product = $productRepository->findOneByReference('instanceimmoe');
                    $productRenew = $productRepository->findOneByReference('renewinstanceimmoe');
                    $nbreVhosts = 1;
                    break;
                case '15s':
                    $product = $productRepository->findOneByReference('instance15s');
                    $productRenew = $productRepository->findOneByReference('renewinstance15s');
                    $nbreVhosts = 100;
                    break;
                case 'cloud':
                    $product = $productRepository->findOneByReference('instancecloud');
                    $productRenew = $productRepository->findOneByReference('renewinstancecloud');
                    $nbreVhosts = 1;
                    break;
                default:
                    throw new \SoapFault('server', 'Type de produit inconnu');
                    break;

            }

            // On regarde si l'instance est déjà sauvée (si c'est le cas, error)


            // On loade le datacenter s'il existe, ou, on le sauve
            $dataCenterRepository = $this->em->getRepository('AppBundle:DataCenter');
            $dataCenter = $dataCenterRepository->findOneByIdGandi($gi['datacenter']['id']);
            if ($dataCenter == null) {
                $dataCenter = new DataCenter();
                $dataCenter->setName($gi['datacenter']['name']);
                $dataCenter->setCountry($gi['datacenter']['iso']);
                $dataCenter->setIso($gi['datacenter']['name']);
                $dataCenter->setIdGandi($gi['datacenter']['id']);
                $dataCenter->setDcCode($gi['datacenter']['dc_code']);
                $this->em->persist($dataCenter);
                $this->em->flush();
            }

            $snapshotProfineRepository = $this->em->getRepository('AppBundle:SnapshotProfileInstance');
            if ($gi['snapshot_profile'] == null) {
                // Id en dur du profil null
                $snapshotProfile = $snapshotProfineRepository->find(2);
            } else {
                $snapshotProfile = $snapshotProfineRepository->findOneByIdGandi($gi['snapshot_profile']['id']);
                if ($snapshotProfile == null) {
                    $snapshotProfile = new SnapshotProfileInstance();
                    $snapshotProfile->setName($gi['snapshot_profile']['name']);
                    $snapshotProfile->setIdGandi($gi['snapshot_profile']['id']);
                    $this->em->persist($snapshotProfile);
                    $this->em->flush();
                }
            }

            $sizeInstanceRepository = $this->em->getRepository('AppBundle:SizeInstance');
            $sizeInstance = $sizeInstanceRepository->findOneByName($gi['size']);
            if ($sizeInstance == null) throw new \SoapFault('Error', 'Taille inconnue');

            $typeInstanceRepository = $this->em->getRepository('AppBundle:TypeInstance');
            $typeInstance = $typeInstanceRepository->findOneByName($gi['type']);
            if ($typeInstance == null) throw new \SoapFault('Error', 'Type d\'instance inconnue');
            // On la sauve.
            $instance = new Instance();
            $instance->setName($gi['name']);
            $instance->setUser($usrSave);
            // On fixe l'instance au produit 5.5
            $instance->setProduct($product);
            $instance->setProductRenew($productRenew);
            $instance->setCatalogName($gi['catalog_name']);
            $instance->setConsole($gi['console']);
            $instance->setDataCenter($dataCenter);
            $instance->setDataDiskAdditionalSize($gi['data_disk_additional_size']);
            $instance->setActive(true);
            $instance->setDataDiskTotalSize($gi['datadisk_total_size']);
            $instance->setUserFtp($gi['user']);
            $instance->setIdGandi($gi['id']);
            $instance->setSizeInstance($sizeInstance);
            $instance->setFtpServer($gi['ftp_server']);
            $instance->setGitServer($gi['git_server']);
            $instance->setNeedUpgrade($gi['need_upgrade']);
            $instance->setSnapshopProfileInstance($snapshotProfile);
            $instance->setTypeInstance($typeInstance);


            $numberVhostsInstanceRepository = $this->em->getRepository('AppBundle:NumberVhostsInstance');
            $objNbreVhosts = $numberVhostsInstanceRepository->findOneByValue($nbreVhosts);
            if ($objNbreVhosts != null) {
                $instance->setNumberMaxVhosts($objNbreVhosts);
            }
            $partHdd = $productRepository->findOneByReference('parthdd');
            $instance->setProductPartHdd($partHdd);

            $dateEnd = new \DateTime();
            $dateEndCommitment = new \DateTime();
            $dateStart = new \DateTime();

            $instance->setDateEnd($gi['date_end'] == null ? null : $dateEnd->setTimestamp($gi['date_end']->timestamp));
            $instance->setDateEndCommitment($gi['date_end_commitment'] == null ? null : $dateEndCommitment->setTimestamp($gi['date_end_commitment']->timestamp));
            $instance->setDateStart($gi['date_start'] == null ? null : $dateStart->setTimestamp($gi['date_start']->timestamp));

            // On sauve
            $this->em->persist($instance);
            $this->em->flush();
            // on sauve les vhosts


            foreach ($gi['vhosts'] as $vhost) {
                $dateCrea = new \DateTime();
                $vhosts = new Vhosts();
                $vhosts->setState($vhost['state']);
                $vhosts->setIdGandi($vhost['id']);
                $vhosts->setName($vhost['name']);
                $vhosts->setDateCrea($dateCrea->setTimestamp($vhost['date_creation']->timestamp));
                $vhosts->setInstance($instance);
                $vhosts->setInMaintenance(0);
                $this->em->persist($vhosts);
            }

            $this->em->flush();

            return true;
        }catch (\Exception $e){
            //throw new \SoapFault('error',$e->getMessage());
            throw new \SoapFault('error','Erreur, merci de rééssayer ultérieurement. Si le problème persiste, merci de contacter votre agence');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idInstance
     * @param bool $asset
     * @return bool
     * @throws \SoapFault
     */
    public function toggleGestionConsole($username, $password, $idInstance,$asset){
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');

        $instance = $instanceRepository->find($idInstance);

        // l'instabce appartient elle à l'utilisateur connecté ? ou l'utilisateur connecté est le gestionnaire ? ou legrain ?

        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) $error = false;
        elseif ($userApi->getId() == $instance->getUser()->getParent()->getId()) $error = false;
        if ($error) throw new \SoapFault('serveur', 'Opération interdite');

        $instance->setGestionConsole($asset);
        $this->em->persist($instance);
        $this->em->flush();
        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idInstance
     * @param bool $asset
     * @return bool
     * @throws \SoapFault
     */
    public function toggleConsole($username, $password, $idInstance,$asset){
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $instance = $instanceRepository->find($idInstance);

        // l'instabce appartient elle à l'utilisateur connecté ? ou l'utilisateur connecté est le gestionnaire ? ou legrain ?


        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) $error = false;
        elseif ($userApi->getId() == $instance->getUser()->getId()) $error = false;
        elseif ($userApi->getId() == $instance->getUser()->getParent()->getId()) $error = false;

        if ($error) throw new \SoapFault('serveur', 'Opération interdite');


        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $gandiApi->paasUpdateConsole($connect, $instance->getIdGAndi(),$asset);
        $instance->setEtatConsole($asset);
        if($asset)$instance->setDateActivationConsole(new \DateTime());
        $this->em->persist($instance);
        $this->em->flush();
        return true;

    }
    /**
     * @param string $domain
     * @return string
     * @throws \SoapFault
     */
    public function whois($domain){

        $whois = new \Whois();

        $result = $whois->lookup($domain,false);
       return json_encode($result);
    }
    /**
     * @param string $username
     * @param string $password
     * @param int $idInstance
     * @return bool
     * @throws \SoapFault
     */
    public function instanceRestart($username, $password, $idInstance)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $instance = $instanceRepository->find($idInstance);

        // l'instabce appartient elle à l'utilisateur connecté ? ou l'utilisateur connecté est le gestionnaire ? ou legrain ?


        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) $error = false;
        elseif ($userApi->getId() == $instance->getUser()->getId()) $error = false;
        elseif ($userApi->getId() == $instance->getUser()->getParent()->getId()) $error = false;

        if ($error) throw new \SoapFault('serveur', 'Opération interdite');


        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $gandiApi->instanceRestart($connect, $instance->getIdGAndi());

        return true;


    }

    /**
     * @param string $username
     * @param string $password
     * @param string $vhost
     * @return bool
     * @throws \SoapFault
     */
    public function delHebergement($username, $password, $vhost)
    {
        $userApi = $this->login($username, $password);
        $vhostsRepository = $this->em->getRepository('AppBundle:Vhosts');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');


        $dvhost = $vhostsRepository->findOneByName($vhost);
        if (!$dvhost) throw new \SoapFault('serveur', 'hébergement inconnu');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');


        // Verif : hebergement appartient à une istance, on récupere son proprio et on regarde si le demandeur est le proprio, son gestionnaire ou legrain
        $user = $dvhost->getInstance()->getUser();
        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) $error = false;
        elseif ($user->getId() == $userApi->getId()) $error = false;
        elseif ($userApi->getId() == $user->getParent()->getId()) $error = false;
        if ($error) throw new \SoapFault('serveur', 'Opération interdite');


        // On supprime le vhost chez Gandi
        $gandiApi = new GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        if ($gandiApi->vhostDelete($connect, $vhost)) {


            // On supprime le vhost de la bdd

            $this->em->remove($dvhost);
            $this->em->flush();


            if (substr($vhost, 0, 4) == 'www.') {

                $domain = substr($vhost, 4, 1000000000);
                // On supprime la redirection de pas 3w vers www si le vhost est www.
                $gandiApi->domainWebredirDelete($connect, $domain, '');
            }
        }
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $host
     * @return \AppBundle\Soap\Entity\WebRedir[]
     * @throws \SoapFault
     */
    public function listWebRedir($username, $password, $domain,$host=null)
    {
        $userApi = $this->login($username, $password);

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        // On load le ndd
        $ndd = $nddRepository->findOneByName($domain);
        if ($ndd == null) throw new \SoapFault('Server', 'Le domaine : ' . $domain . ' n\'a pas été trouvé dans notre base de données');


        $allowed = false;
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        // On regarde si le demandeur est legrain, le gestionnaire du proprio du ndd ou, le proprio du ndd
        if (($userApi->getRoles()->contains($roleLegrain)) || ($userApi->getId() == $ndd->getUser()->getId()) || ($userApi->getId() == $ndd->getUser()->getParent()->getId())) {
            $allowed = true;
        }
        if (!$allowed) throw new \SoapFault('server', 'Accès interdit');

        $list = $this->privateListWebRedir($domain,$host);


        $return = array();
        foreach ($list as $item) {
            $return[] = new \AppBundle\Soap\Entity\WebRedir($item['host'], $item['type'], $item['url']);
        }
        return $return;

    }


    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $host
     * @param string $url
     * @param string $type
     * @return boolean
     * @throws \SoapFault
     */
    public function createWebRedir($username, $password, $domain, $host, $url, $type){

        try {
            $userApi = $this->login($username, $password);

            $nddRepository = $this->em->getRepository('AppBundle:Ndd');
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            // On load le ndd
            $ndd = $nddRepository->findOneByName($domain);
            if ($ndd == null) throw new \SoapFault('Server', 'Le domaine : ' . $domain . ' n\'a pas été trouvé dans notre base de données');


            $allowed = false;
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

            // On regarde si le demandeur est legrain, le gestionnaire du proprio du ndd ou, le proprio du ndd
            if (($userApi->getRoles()->contains($roleLegrain)) || ($userApi->getId() == $ndd->getUser()->getId()) || ($userApi->getId() == $ndd->getUser()->getParent()->getId())) {
                $allowed = true;
            }
            if (!$allowed) throw new \SoapFault('server', 'Accès interdit');

            $gandiApi = new \GandiBundle\Controller\GandiController();
            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            // Connect $connect,$domain,$host,$url,$type
            $gandiApi->domainWebredirCreate($connect, $domain, $host, $url, $type);
            return true;
        }catch (\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $host
     * @return boolean
     * @throws \SoapFault
     */
    public function deleteWebRedir($username, $password, $domain, $host){

        try {
            $userApi = $this->login($username, $password);

            $nddRepository = $this->em->getRepository('AppBundle:Ndd');
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            // On load le ndd
            $ndd = $nddRepository->findOneByName($domain);
            if ($ndd == null) throw new \SoapFault('Server', 'Le domaine : ' . $domain . ' n\'a pas été trouvé dans notre base de données');


            $allowed = false;
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

            // On regarde si le demandeur est legrain, le gestionnaire du proprio du ndd ou, le proprio du ndd
            if (($userApi->getRoles()->contains($roleLegrain)) || ($userApi->getId() == $ndd->getUser()->getId()) || ($userApi->getId() == $ndd->getUser()->getParent()->getId())) {
                $allowed = true;
            }
            if (!$allowed) throw new \SoapFault('server', 'Accès interdit');

            $gandiApi = new \GandiBundle\Controller\GandiController();
            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            // Connect $connect,$domain,$host,$url,$type
            $gandiApi->domainWebredirDelete($connect, $domain, $host);
            return true;
        }catch (\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }
    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $host
     * @param string $newhost
     * @param string $newurl
     * @param string $newtype
     * @return boolean
     * @throws \SoapFault
     */
    public function updateWebRedir($username, $password, $domain, $host, $newhost,$newurl, $newtype){

        try {
            $userApi = $this->login($username, $password);

            $nddRepository = $this->em->getRepository('AppBundle:Ndd');
            $roleRepository = $this->em->getRepository('AppBundle:Roles');
            // On load le ndd
            $ndd = $nddRepository->findOneByName($domain);
            if ($ndd == null) throw new \SoapFault('Server', 'Le domaine : ' . $domain . ' n\'a pas été trouvé dans notre base de données');


            $allowed = false;
            $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

            // On regarde si le demandeur est legrain, le gestionnaire du proprio du ndd ou, le proprio du ndd
            if (($userApi->getRoles()->contains($roleLegrain)) || ($userApi->getId() == $ndd->getUser()->getId()) || ($userApi->getId() == $ndd->getUser()->getParent()->getId())) {
                $allowed = true;
            }
            if (!$allowed) throw new \SoapFault('server', 'Accès interdit');

            $gandiApi = new \GandiBundle\Controller\GandiController();
            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
            // Connect $connect,$domain,$host,$url,$type
            $gandiApi->domainWebredirUpdate($connect, $domain, $host,$newhost, $newurl, $newtype);
            return true;
        }catch (\Exception $e){
            throw new \SoapFault('e',$e->getMessage());
        }
    }


    private function privateListWebRedir($domain, $host = null)
    {
        // On va chercher la liste chez Gandi.
        $gandiApi = new \GandiBundle\Controller\GandiController();
        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


        return $gandiApi->domainWebredirList($connect, $domain, $host);
    }



    /**
     * @param string $username
     * @param string $password
     * @param string $contactName
     * @param string $jsonParameters
     * @return bool
     * @throws \SoapFault
     */
    public function addComplementInformationContact($username, $password, $contactName, $jsonParameters)
    {
        $userApi = $this->login($username, $password);

        $parameters = json_decode($jsonParameters, true);

        $contactRepository = $this->em->getRepository('AppBundle:Contact');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $contact = $contactRepository->findOneByCode($contactName);
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');

        $error = true;

        if ($userApi->getRoles()->contains($roleLegrain)) $error = false;
        else {
            if ($contact->getUser()->getId() == $userApi->getId()) {
                $error = false;
            } elseif ($userApi->getRoles()->contains($roleAgence) && $userApi->getAgency()->getId() == $contact->getUser()->getAgency()->getId()) $error = false;
        }
        if ($error) throw new \SoapFault('e', 'Accès interdit');
        //
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);
        try {
            $gandiApi->contactUpdate($connect, $contact->getCodeGandi(), $parameters);
            return true;
        } catch (\SoapFault $e) {
            throw new \SoapFault('e', $e->getMessage());
        }

    }

    /**
     * Retourne un json avec les infos relatives au ndd
     * @param string $contact
     * @param string $domain
     * @return string
     */
    public function canAssociateDomain($contact, $domain)
    {
        $contactRepository = $this->em->getRepository('AppBundle:Contact');

        $contact = $contactRepository->findOneByCode($contact);
        $codeGandi = $contact->getCodeGandi();
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $canAssociateDomain = $gandiApi->canAssociateDomain($connect, $codeGandi, $domain);

        if ($canAssociateDomain == 1) return json_encode(array('error' => false));
        else {
            $return = array();
            foreach ($canAssociateDomain as $test) {
                $return[] = array('error' => $test['error'], 'reason' => $test['reason']);
            }
//            return json_encode($canAssociateDomain);
            return json_encode(array('error' => true, 'otherwire' => $return));
        }
    }

    /**
     * json parameter doit contenir au minima les infos : type,user,name,firstname,phone,email,address,city
     * @param string $username
     * @param string $password
     * @param $string jsonParameters
     * @return \AppBundle\Soap\Entity\Contact
     * @throws \SoapFault
     */
    public function saveContact($username, $password, $jsonParameters)
    {
        $userApi = $this->login($username, $password);

        $parameters = json_decode($jsonParameters);

        if (!property_exists($parameters, 'type')) throw new \SoapFault('error', 'Le type de contact doit être renseigné');
        if (!property_exists($parameters, 'user')) throw new \SoapFault('error', 'L\'utilisateur associé au contact doit être renseigné');
        if (!property_exists($parameters, 'name')) throw new \SoapFault('error', 'Le nom du contact doit être renseigné');
        if (!property_exists($parameters, 'firstname')) throw new \SoapFault('error', 'Le nom du contact doit être renseigné');
        if (!property_exists($parameters, 'phone')) throw new \SoapFault('error', 'Le numéro de téléphone associé au contact doit être renseigné');
        if (!property_exists($parameters, 'email')) throw new \SoapFault('error', 'L\'e-mail associé au contact doit être renseigné');
        if (!property_exists($parameters, 'address')) throw new \SoapFault('error', 'L\'adresse postale associée au contact doit être renseignée');
        if (!property_exists($parameters, 'city')) throw new \SoapFault('error', 'La ville postale associée au contact doit être renseignée');


        try {
            $gandiApi = new \GandiBundle\Controller\GandiController();

            $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
            $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

            $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

            $options = array();
            $options['city'] = $parameters->city;
            $options['country'] = $parameters->state;
            $options['email'] = $this->email_gandi_per_default;
            $options['family'] = $parameters->name;
            $options['given'] = $parameters->firstname;
            $options['password'] = 'v' . time() . '%';
            $options['phone'] = $parameters->phone;
            $options['streetaddr'] = $parameters->address;
            $options['type'] = (int)$parameters->type;
            $options['accept_contract'] = true;
            $options['mail_obfuscated'] = true;
            $options['third_part_resell'] = false;
            $options['lang'] = 'fr';
            $options['newsletter'] = false;
            $options['data_obfuscated'] = true;
            if (property_exists($parameters, 'zipCode')) $options['zip'] = $parameters->zipCode == null ? '' : $parameters->zipCode;
            if (property_exists($parameters, 'num_tva')) $options['vat_number'] = $parameters->num_tva == null ? '' : $parameters->num_tva;
            if (property_exists($parameters, 'num_marque') && $parameters->num_marque != null) $options['brand_number'] = $parameters->num_marque;

            if ($options['type'] != 0) {
                if (property_exists($parameters, 'company_name')) $options['orgname'] = $parameters->company_name == null ? '' : $parameters->company_name;
            }


            try {
                $contact = $gandiApi->contactCreate($connect, $options);
            } catch (\Exception $e) {
                throw new \SoapFault('error', $this->returnErrorsCreatingContact($e));


            }

            $codeGandi = $contact['handle'];

            $dContact = new Contact();

            $dContact->setCodeGandi($codeGandi);
            $dContact->setCode(str_replace('GANDI', 'GWI', $codeGandi));
            $dContact->setIdGandi($contact['id']);
            // On récupère le mail associé.

            $dContact->setFakeEmail($parameters->email);
            $dContact->setEmail($contact['email']);
            $dContact->setName($contact['family']);
            $dContact->setFirstname($contact['given']);
            $dContact->setUser($this->em->getRepository('AppBundle:User')->find($parameters->user));
            $this->em->persist($dContact);
            $this->em->flush();


        } catch (\SoapFault $e) {
            throw new \SoapFault('error', $e->getMessage());
        }

        return new \AppBundle\Soap\Entity\Contact($dContact->getId(), $dContact->getFakeEmail(), $dContact->getFakeEmail(), $dContact->getCode(), $dContact->getIsDefault(), $dContact->getName(), $dContact->getFirstname(), $dContact->getUser()->getCodeClient());
    }

    private function returnErrorsCreatingContact(\Exception $e)
    {
        switch ($e->getMessage()) {

            case (stristr($e->getMessage(), 'zipcode') !== FALSE) :
                $message = "Ce code postal est introuvable pour le pays sélectionné ";
                break;
            case (stristr($e->getMessage(), 'orgname') !== FALSE) :
                $message = "Vous devez renseigner le nom de la société ";
                break;
            case (stristr($e->getMessage(), 'phone') !== FALSE) :
                $message = "Le numéro de téléphone est incorrect ";
                break;
            default:
                //    $message =  json_encode($e);
                $message = "Une erreur s'est produite, merci d'appeler votre revendeur";
                break;
        }

        return $message;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param string $subdomain
     * @param string $domain
     * @param int $idInstance
     * @return bool
     * @throws \SoapFault
     */
    public function addHebergement($username, $password, $idUser, $subdomain, $domain, $idInstance)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $user = $userRepository->find($idUser);
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $vhost = ($subdomain == '' ? 'www' : $subdomain) . '.' . $domain;
        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }
        $instance = $instanceRepository->find($idInstance);
        if (!$instance) throw new \SoapFault('server', 'serveur introuvable');

        // On regarde si l'instance appartient à l'utilisateur, son gestionnaire ou, à legrain (dans certains cas).
        if ($instance->getUser()->getId() != $user->getId()) {
            // On regarde si l'instance appartient à son gestionnaire ou à legrain s'il n'a pas de gestionnaire
            if ($user->getParent()) {
                if (!$userApi->getRoles()->contains($roleLegrain)) {
                    if ($userApi->getId() != $user->getParent()->getId()) throw new \SoapFault('server', 'accès refusé');
                }
            } else {
                if (!$userApi->getRoles()->contains($roleLegrain)) throw new \SoapFault('server', 'accès refusé');
            }
        }

        if ($instance->getIsMutu()) throw new \SoapFault('server', 'Impossible \'installer un vhost sur un serveur mutualisé');
        $p = $instance->getProduct();

        $features = $p->getFeatures();
        //$maxVhosts = $features->nombreVhostsMax;
        $maxVhosts = $instance->getNumberMaxVhosts()->getValue();

        if ($userApi->getRoles()->contains($roleLegrain)) {
            $maxVhosts = 100;
        }
        $listVhosts = $instance->getVhosts();
        // On regarde si le vhosts n'est pas déjà dans la liste
        foreach ($listVhosts as $v) {
            if ($v->getName() == $vhost) throw new \SoapFault('Server', 'Vhost déjà présent');
        }
        // On regarde si on peut ajouter un vhosts à l'instance.
        if ($maxVhosts <= count($listVhosts)) throw new \SoapFault('server', 'Impossible d\'ajouter un nouveau VHOST au serveur');
        // On regarde si les DNS du domaine sont bien sur a.dns.gandi.net
        $ndd = $nddRepository->findOneByName($domain);


        $infoDomain = $gandiApi->infosDomain($connect, $ndd->getName());

        if (!in_array('a.dns.gandi.net', $infoDomain->nameservers)) throw new \SoapFault('server', 'Paramètres DNS du nom de domaine incorrect');


        // On regarde si la redirection existe déjà chez gandi
        $host = $this->privateListWebRedir($domain, '');
        if (!empty($host)) {
            // Si elle existe, on la supprime avant de creer le Vhost
            $gandiApi->domainWebredirDelete($connect, $domain, '');
        }

        // Api gandi, on ajoute le vhosts
        // On ajoute le vhosts à l'instance
        // Api gandi on bouge les DNS vers l'instance
        $res = $gandiApi->vhostsCreate($connect, $instance->getIdGandi(), $vhost);
        // API gandi, si subdomain = www, on crait la redirection rien vers wwww.
        if (($subdomain == '' ? 'www' : $subdomain) == 'www') {
            //domainWebredirCreate( $connect,'chasselas.fr','','http://www.chasselas.fr','http301');
            $gandiApi->domainWebredirCreate($connect, $domain, '', 'http://' . $vhost, 'http301');
        }

        return true;
    }




    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param string $subdomain
     * @param string $domain
     * @param int $idHosting
     * @return bool
     * @throws \SoapFault
     */
    public function addHebergementMutualise($username, $password, $idUser, $subdomain, $domain, $idHosting)
    {
        $userApi = $this->login($username, $password);
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');

        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $user = $userRepository->find($idUser);
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $vhost = ($subdomain == '' ? 'www' : $subdomain) . '.' . $domain;
        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }
        $hosting = $hostingRepository->find($idHosting);
        if (!$hosting) throw new \SoapFault('server', 'hébergement introuvable');

        // On regarde si l'instance appartient à l'utilisateur, son gestionnaire ou, à legrain (dans certains cas).
        if ($hosting->getUser()->getId() != $user->getId()) {
            // On regarde si l'instance appartient à son gestionnaire ou à legrain s'il n'a pas de gestionnaire
            if ($user->getParent()) {
                if (!$userApi->getRoles()->contains($roleLegrain)) {
                    if ($userApi->getId() != $user->getParent()->getId()) throw new \SoapFault('server', 'accès refusé');
                }
            } else {
                if (!$userApi->getRoles()->contains($roleLegrain)) throw new \SoapFault('server', 'accès refusé');
            }
        }
        $p = $hosting->getProductHosting();

        $features = $p->getFeatures();
        $instance = $hosting->getProductHosting()->getInstance();
        //$maxVhosts = $features->nombreVhostsMax;
        $maxVhosts = $instance->getNumberMaxVhosts()->getValue();

        if ($userApi->getRoles()->contains($roleLegrain)) {
            $maxVhosts = 100;
        }
        $listVhosts = $instance->getVhosts();
        // On regarde si le vhosts n'est pas déjà dans la liste
        foreach ($listVhosts as $v) {
            if ($v->getName() == $vhost) throw new \SoapFault('Server', 'Vhost déjà présent');
        }
        // On regarde si on peut ajouter un vhosts à l'instance.
        if ($maxVhosts <= ($instance->getNbVhosts() + $instance->getNbEmptyHerberMutu())) throw new \SoapFault('server', 'Impossible d\'ajouter un nouveau VHOST au serveur');
        // On regarde si les DNS du domaine sont bien sur a.dns.gandi.net
        $ndd = $nddRepository->findOneByName($domain);


        $infoDomain = $gandiApi->infosDomain($connect, $ndd->getName());

        if (!in_array('a.dns.gandi.net', $infoDomain->nameservers)) throw new \SoapFault('server', 'Paramètres DNS du nom de domaine incorrect');


        // On regarde si la redirection existe déjà chez gandi
        $host = $this->privateListWebRedir($domain, '');
        if (!empty($host)) {
            // Si elle existe, on la supprime avant de creer le Vhost
            $gandiApi->domainWebredirDelete($connect, $domain, '');
        }

        // Api gandi, on ajoute le vhosts
        // On ajoute le vhosts à l'instance
        // Api gandi on bouge les DNS vers l'instance
        $res = $gandiApi->vhostsCreate($connect, $instance->getIdGandi(), $vhost);
        $hosting->setVhost($vhost);

        // API gandi, si subdomain = www, on crait la redirection rien vers wwww.
        if (($subdomain == '' ? 'www' : $subdomain) == 'www') {
            //domainWebredirCreate( $connect,'chasselas.fr','','http://www.chasselas.fr','http301');
            $gandiApi->domainWebredirCreate($connect, $domain, '', 'http://' . $vhost, 'http301');
        }
        $this->em->persist($hosting);
        $this->em->flush();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Hosting[]
     * @throws \SoapFault
     */
    public function listHebergementsMutuDisponibles($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $user = $userRepository->find($idUser);
        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }
        try {
            // Instances de l'utilisateur, si le demandeur est un gestionaire, celles du gestionnaire.
            $instancesUser = $hostingRepository->findBy(array('user' => $user->getId(), 'vhost' => null), array('dateEnding' => 'ASC'));
            $instancesGestionnaire = array();
            $instancesLegrain = array();

            // Si l'utilisateur est un gestionnaire, celles de legrain.
            if (($userApi->getRoles()->contains($roleLegrain)) && $user->getRoles()->contains($roleGestionnaire)) {
                // On récupère la liste des instances legrain
                $instancesLegrain = $hostingRepository->findBy(array('user' => $userApi->getId(), 'vhost' => null), array('dateEnding' => 'ASC'));
                // Celles de l'agence
            } elseif ($userApi->getId() != $user->getId() && $userApi->getRoles()->contains($roleGestionnaire)) {
                // On récupere le parent de l'utilisateur (son gestionnaire)
                $parent = $user->getParent();
                $instancesGestionnaire = $hostingRepository->findBy(array('user' => $parent->getId(), 'vhost' => null), array('dateEnding' => 'ASC'));
            }


            $instances = array_merge($instancesUser, $instancesGestionnaire);
            $instances = array_merge($instances, $instancesLegrain);

            $return = array();
            foreach ($instances as $item) {
                $user = $this->getCustomer($username, $password, $item->getUser()->getId());
                $productHosting = $this->getProductsHosting($username, $password, $item->getProductHosting()->getId());
                $return[] = new \AppBundle\Soap\Entity\Hosting($item->getId(), $item->getVhost(), $item->getDateEnding(), $productHosting, $user);
            }
            return $return;
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Instance[]
     * @throws \SoapFault
     */
    public function listInstancesAvailableForHosting($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleGestionnaire = $roleRepository->findOneByName('ROLE_AGENCE');
        $user = $userRepository->find($idUser);

        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        // Instances de l'utilisateur, si le demandeur est un gestionaire, celles du gestionnaire.
        $instancesUser = $instanceRepository->findBy(array('user' => $user->getId(), 'isMutu' => false), array('dateEnd' => 'ASC'));
        $instancesGestionnaire = array();
        $instancesLegrain = array();

        // Si l'utilisateur est un gestionnaire, celles de legrain.
        if (($userApi->getRoles()->contains($roleLegrain)) && $user->getRoles()->contains($roleGestionnaire)) {
            // On récupère la liste des instances legrain
            $instancesLegrain = $instanceRepository->findBy(array('user' => $userApi->getId(), 'isMutu' => false), array('dateEnd' => 'ASC'));
            // Celles de l'agence
        } elseif ($userApi->getId() != $user->getId() && $userApi->getRoles()->contains($roleGestionnaire)) {
            // On récupere le parent de l'utilisateur (son gestionnaire)
            $parent = $user->getParent();
            $instancesGestionnaire = $instanceRepository->findBy(array('user' => $parent->getId(), 'isMutu' => false), array('dateEnd' => 'ASC'));
        }


        $instances = array_merge($instancesUser, $instancesGestionnaire);
        $instances = array_merge($instances, $instancesLegrain);
        $return = array();
        $unique = array();
        try {
            foreach ($instances as $i) {
                if (!in_array($i->getId(), $unique)) {
                    $unique[] = $i->getId();
                    $d = $i->getDataCenter();
                    $p = $i->getProduct();
                    $pr = $i->getProductRenew();

                    $features = $p->getFeatures();
                    $gandiTotalDataDiskSize = $i->getDataDiskAdditionalSize() + 10;
                    $productRepository = $this->em->getRepository('AppBundle:Product');
                    $optionInstance = $productRepository->findOneByReference('parthdd');
                    $maxVhosts = $i->getNumberMaxVhosts()->getValue();
                    //$maxVhosts = $features->nombreVhostsMax;


                    if ($userApi->getRoles()->contains($roleLegrain)) {
                        $maxVhosts = 100;
                    }
                    // if ($maxVhosts > count($i->getVhosts())) {
                    if ($maxVhosts > ((int)$i->getNbVhosts() + (int)$i->getNbEmptyHerberMutu())) {
                        $return[] = new \AppBundle\Soap\Entity\Instance(
                            $i->getId(),//$id,
                            $i->getCatalogName(),//$catalogName,
                            $i->getConsole(),//$console,
                            $gandiTotalDataDiskSize - $features->tailleDisque,//$dataDiskAdditionalSize,
                            ($gandiTotalDataDiskSize - $features->tailleDisque) / $optionInstance->getFeatures()->part,  // $quantityPartDataDiskAdditionalSize
                            $gandiTotalDataDiskSize,//$dataDiskTotalSize,
                            $i->getDateEnd(),//$dateEnd,
                            $i->getDateEndCommitment(),//$dateEndCommitment,
                            $i->getDateStart(),//$dateStart,
                            $i->getFtpServer(),//$ftpServer,
                            $i->getGitServer(),//$gitServer,
                            $i->getName(),//$name,
                            $i->getNeedUpgrade(),//$needUpgrade,
                            $i->getUserFtp(),//$userFtp,
                            null,//$vhosts,
                            new \AppBundle\Soap\Entity\DataCenter($d->getId(), $d->getCountry(), $d->getDcCode(), $d->getIso(), $d->getName()),//$dataCenter,
                            $this->getProduct($username, $password, $p->getid(), $idUser),//$product,
                            ($i->getSizeInstance() ? $i->getSizeInstance()->getName() : null),// sizeInstance
                            ($i->getSnapshopProfileInstance() ? new SnapshotProfile($i->getSnapshopProfileInstance()->getId(), $i->getSnapshopProfileInstance()->getName(), null, $i->getSnapshopProfileInstance()->getProduct()->getId()) : null),//$snapshopProfileInstance,
                            ($i->getTypeInstance() ? $i->getTypeInstance()->getName() : null),//$typeInstance,
                            $i->getActive(),//$active
                            $this->getProduct($username, $password, $pr->getid(), $idUser), //$productRenew
                            $this->getProduct($username, $password, $optionInstance, $idUser) //productPartHdd

                        );
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\SizeInstance[]
     * @throws \SoapFault
     */
    public function listSizesInstances($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        $userRepository = $this->em->getRepository('AppBundle:User');
        $sizeInstanceRepository = $this->em->getRepository('AppBundle:SizeInstance');
        $user = $userRepository->find($idUser);
        // On récupère la liste des instances
        $sizes = $sizeInstanceRepository->findAll();
        $return = array();
        foreach ($sizes as $size) {

            $product = $this->getProduct($username, $password, $size->getProduct()->getId(), $idUser);
            $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;
            $return[] = new \AppBundle\Soap\Entity\SizeInstance($size->getId(), $size->getName(), $pricePerMonth, $size->getProduct()->getId());

        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\NumberVhostsInstance[]
     * @throws \SoapFault
     */
    public function listNbreVhostsInstance($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        // $userRepository = $this->em->getRepository('AppBundle:User');
        $numberVhostsInstanceRepository = $this->em->getRepository('AppBundle:NumberVhostsInstance');
        //  $user = $userRepository->find($idUser);
        // On récupère la liste des instances
        $nbres = $numberVhostsInstanceRepository->findBy(array(), array('value' => 'ASC'));
        $return = array();
        foreach ($nbres as $nbre) {

            $product = $this->getProduct($username, $password, $nbre->getProduct()->getId(), $idUser);
            $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;
            $return[] = new \AppBundle\Soap\Entity\NumberVhostsInstance($nbre->getId(), $nbre->getName(), $nbre->getValue(), $pricePerMonth, $nbre->getProduct()->getId());

        }
        return $return;
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\SnapshotProfile[]
     * @throws \SoapFault
     */
    public function listProfilSauvegardeInstance($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        // $userRepository = $this->em->getRepository('AppBundle:User');
        $snapshopProfileInstanceRepository = $this->em->getRepository('AppBundle:SnapshotProfileInstance');
        //  $user = $userRepository->find($idUser);
        // On récupère la liste des profils de sauvegardes

        $profils = $snapshopProfileInstanceRepository->findAll();
        $return = array();
        foreach ($profils as $profil) {

            $product = $this->getProduct($username, $password, $profil->getProduct()->getId(), $idUser);
            $pricePerMonth = $product->minPriceHT ? $product->minPriceHT : $product->priceHT;
            $return[] = new \AppBundle\Soap\Entity\SnapshotProfile($profil->getId(), $product->name, $pricePerMonth, $profil->getProduct()->getId());

        }


        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Instance[]
     * @throws \SoapFault
     */
    public function listAllInstances($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $roleAgence = $roleRepository->findOneByName('ROLE_AGENCE');

        $user = $userRepository->find($idUser);


        if (!$user->getRoles()->contains($roleAgence)) throw new \SoapFault('access forbidden', 'Niveau d\'accès insuffisant');
        if ($user->getRoles()->contains($roleLegrain)) {
            $instances = $instanceRepository->findAll();
        } else {
            $instances = $instanceRepository->findByAgency($user->getAgency());
        }
        $return = array();
        foreach ($instances as $i) {


            $d = $i->getDataCenter();
            $p = $i->getProduct();
            $pr = $i->getProductRenew();

            $features = $p->getFeatures();
            $gandiTotalDataDiskSize = $i->getDataDiskAdditionalSize() + 10;
            $productRepository = $this->em->getRepository('AppBundle:Product');
            $optionInstance = $productRepository->findOneByReference('parthdd');


            $u = $i->getUser();


            $ci = new City($u->getCity()->getId(), $u->getCity()->getName(), $u->getCity()->getCodeInsee());
            $zi = new ZipCode($u->getZipcode()->getId(), $u->getZipcode()->getName());

            $a = $u->getAgency();
            $agCi = new City($a->getCity()->getId(), $a->getCity()->getName(), $a->getCity()->getCodeInsee());
            $agZi = new ZipCode($a->getZipcode()->getId(), $a->getZipcode()->getName());
            //$ag = new \AppBundle\Soap\Entity\Agency($a->getId(),$a->getName(),$a->getSiret(),$a->getAddress1(),$a->getAddress2(),$a->getAddress3(),$agCi,$agZi,$a->getPhone(),$a->getEmail(), $a->getWebsite(),null,null,null);
            $ag = $this->_getAgency($a);
            $usrinst = new \AppBundle\Soap\Entity\User($u->getId(), $u->getName(), $u->getFirstname(), $u->getEmail(), $u->getAddress1(), $u->getAddress2(), $u->getAddress3(),
                $ci, $zi, $u->getPhone(), $u->getActive(), $ag, ($u->getParent() == null ? true : false), $u->getCellPhone(), $u->getWorkPhone(), $u->getCompanyName()
                , $u->getCodeClient(), $u->getNumTVA(), null);


            $return[] = new \AppBundle\Soap\Entity\Instance(
                $i->getId(),//$id,
                $i->getCatalogName(),//$catalogName,
                $i->getConsole(),//$console,
                $gandiTotalDataDiskSize - $features->tailleDisque,//$dataDiskAdditionalSize,
                ($gandiTotalDataDiskSize - $features->tailleDisque) / $optionInstance->getFeatures()->part,  // $quantityPartDataDiskAdditionalSize
                $gandiTotalDataDiskSize,//$dataDiskTotalSize,
                $i->getDateEnd(),//$dateEnd,
                $i->getDateEndCommitment(),//$dateEndCommitment,
                $i->getDateStart(),//$dateStart,
                $i->getFtpServer(),//$ftpServer,
                $i->getGitServer(),//$gitServer,
                $i->getName(),//$name,
                $i->getNeedUpgrade(),//$needUpgrade,
                $i->getUserFtp(),//$userFtp,
                null,//$vhosts,
                new \AppBundle\Soap\Entity\DataCenter($d->getId(), $d->getCountry(), $d->getDcCode(), $d->getIso(), $d->getName()),//$dataCenter,
                $this->getProduct($username, $password, $p->getid(), $idUser),//$product,
                ($i->getSizeInstance() ? $i->getSizeInstance()->getName() : null),// sizeInstance
                ($i->getSnapshopProfileInstance() ? $i->getSnapshopProfileInstance()->getName() : null),//$snapshopProfileInstance,
                ($i->getTypeInstance() ? $i->getTypeInstance()->getName() : null),//$typeInstance,
                $i->getActive(),//$active
                $this->getProduct($username, $password, $pr->getid(), $idUser), //$productRenew
                $this->getProduct($username, $password, $optionInstance, $idUser), //productPartHdd
                null,
                $usrinst,
                null,
                $i->getFreeDisk(),
                $i->getUsedDisk()

            );

        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param bool $mutualisable
     * @return \AppBundle\Soap\Entity\Instance[]
     * @throws \SoapFault
     */
    private function listInstancesByType($username, $password, $idUser, $mutualisable = false)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $user = $userRepository->find($idUser);


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if ($mutualisable) {
            // $where['reference']='instancemutualisable';
            $instances = $instanceRepository->findByProductReferenceAndUser('instancemutualisable', $user);

        } else {
            $instances = $instanceRepository->findBy(array('user' => $user->getId()), array('dateEnd' => 'ASC'));
        }
        $return = array();
        foreach ($instances as $i) {


            $d = $i->getDataCenter();
            $p = $i->getProduct();
            $pr = $i->getProductRenew();

            $features = $p->getFeatures();
            $gandiTotalDataDiskSize = $i->getDataDiskAdditionalSize() + 10;
            $productRepository = $this->em->getRepository('AppBundle:Product');
            $optionInstance = $productRepository->findOneByReference('parthdd');

            $return[] = new \AppBundle\Soap\Entity\Instance(
                $i->getId(),//$id,
                $i->getCatalogName(),//$catalogName,
                $i->getConsole(),//$console,
                $gandiTotalDataDiskSize - $features->tailleDisque,//$dataDiskAdditionalSize,
                ($gandiTotalDataDiskSize - $features->tailleDisque) / $optionInstance->getFeatures()->part,  // $quantityPartDataDiskAdditionalSize
                $gandiTotalDataDiskSize,//$dataDiskTotalSize,
                $i->getDateEnd(),//$dateEnd,
                $i->getDateEndCommitment(),//$dateEndCommitment,
                $i->getDateStart(),//$dateStart,
                $i->getFtpServer(),//$ftpServer,
                $i->getGitServer(),//$gitServer,
                $i->getName(),//$name,
                $i->getNeedUpgrade(),//$needUpgrade,
                $i->getUserFtp(),//$userFtp,
                null,//$vhosts,
                new \AppBundle\Soap\Entity\DataCenter($d->getId(), $d->getCountry(), $d->getDcCode(), $d->getIso(), $d->getName()),//$dataCenter,
                $this->getProduct($username, $password, $p->getid(), $idUser),//$product,
                ($i->getSizeInstance() ? $i->getSizeInstance()->getName() : null),// sizeInstance
                ($i->getSnapshopProfileInstance() ? new SnapshotProfile($i->getSnapshopProfileInstance()->getId(), $i->getSnapshopProfileInstance()->getName(), null, $i->getSnapshopProfileInstance()->getProduct()->getId()) : null),//$snapshopProfileInstance,
                ($i->getTypeInstance() ? $i->getTypeInstance()->getName() : null),//$typeInstance,
                $i->getActive(),//$active
                $this->getProduct($username, $password, $pr->getid(), $idUser), //$productRenew
                $this->getProduct($username, $password, $optionInstance, $idUser) //productPartHdd
                , null, null, null, $i->getFreeDisk(), $i->getUsedDisk(),$i->getGestionConsole(),$i->getEtatConsole()

            );

        }

        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Instance[]
     * @throws \SoapFault
     */
    public function listInstancesMutualisables($username, $password, $idUser)
    {
        return $this->listInstancesByType($username, $password, $idUser, true);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return \AppBundle\Soap\Entity\Instance[]
     * @throws \SoapFault
     */

    public function listInstances($username, $password, $idUser)
    {
        return $this->listInstancesByType($username, $password, $idUser);
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idInstance
     * @return float
     * @throws \SoapFault
     */
    public function nombrePartHddAvaillableForInstance($username, $password, $idInstance)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');

        $instance = $instanceRepository->find($idInstance);
        if (!$instance) throw new \SoapFault('error', 'Cette instance n\'existe pas');

        $p = $instance->getProduct();
        $maxSizeDisk = 1000;
        $features = $p->getFeatures();
        $gandiTotalDataDiskSize = $instance->getDataDiskAdditionalSize() + 10;
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $optionInstance = $productRepository->findOneByReference('parthdd');

        $sizeAvaillable = $maxSizeDisk - $features->tailleDisque;

        return $sizeAvaillable / $optionInstance->getFeatures()->part;

        /*
                $gandiTotalDataDiskSize-$features->tailleDisque,//$dataDiskAdditionalSize,
                    ($gandiTotalDataDiskSize-$features->tailleDisque)/$optionInstance->getFeatures()->part,  // $quantityPartDataDiskAdditionalSize
                    $gandiTotalDataDiskSize,//$dataDiskTotalSize,
        */
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $ndd
     * @param string $vhost
     * @return \AppBundle\Soap\Entity\VhostsSimplified
     * @throws \SoapFault
     */
    public function getVhostsPerNddAndVhost($username, $password, $ndd, $vhost)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $vhostsRepository = $this->em->getRepository('AppBundle:Vhosts');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $ndd = $nddRepository->findOneByName($ndd);
        if (!$ndd) throw new \SoapFault('server', 'Ndd introuvable');
        $user = $ndd->getUser();
        $parentOrLegrain = false;

        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        $parentOrLegrain = true;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        } else {
            $parentOrLegrain = true;
        }


        // Liste des vhosts pour un ndd
        $dVhost = $vhostsRepository->findOneByName($vhost);
        if (!$dVhost) throw new \SoapFault('FORBIDDEN', 'Vhost inconnu');
        $serverName = null;
        $serverId = null;
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
        $hosting = $hostingRepository->findOneByVhost($vhost);

        if ($dVhost->getInstance() != null) {
            if (($dVhost->getInstance()->getUser()->getId() == $ndd->getUser()->getId()) || $parentOrLegrain) {
                $serverName = $dVhost->getInstance()->getName();
                $serverId = $dVhost->getInstance()->getId();
            }


        }
        return new \AppBundle\Soap\Entity\VhostsSimplified($dVhost->getId(), $dVhost->getDateCrea(), $dVhost->getName(), $serverName, $serverId, $dVhost->getInMaintenance(), ($hosting ? true : false));
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $ndd
     * @return \AppBundle\Soap\Entity\VhostsSimplified[]
     * @throws \SoapFault
     */
    public function getVhostsPerNdd($username, $password, $ndd)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $vhostsRepository = $this->em->getRepository('AppBundle:Vhosts');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');
        $parentOrLegrain = false;
        $ndd = $nddRepository->findOneByName($ndd);
        if (!$ndd) throw new \SoapFault('server', 'Ndd introuvable');
        $user = $ndd->getUser();


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if (($userApi->getId() == $user->getId())) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        $parentOrLegrain = true;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        } else {
            $parentOrLegrain = true;
        }


        $vhosts = array();
        // Liste des vhosts pour un ndd
        $dVhosts = $vhostsRepository->findByNdd($ndd);
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
        foreach ($dVhosts as $v) {
            $serverName = null;
            $serverId = null;
            if ($v->getInstance() != null) {
                if (($v->getInstance()->getUser()->getId() == $ndd->getUser()->getId()) || $parentOrLegrain) {

                    $serverName = $v->getInstance()->getName();
                    $serverId = $v->getInstance()->getId();
                }
            }
            $hosting = $hostingRepository->findOneByVhost($v->getName());
            $vhosts[] = new \AppBundle\Soap\Entity\VhostsSimplified($v->getId(), $v->getDateCrea(), $v->getName(), $serverName, $serverId, $v->getInMaintenance(), ($hosting ? true : false));
        }
        return $vhosts;
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idInstance
     * @return \AppBundle\Soap\Entity\Instance
     * @throws \SoapFault
     */
    public function getInstance($username, $password, $idInstance)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $instance = $instanceRepository->find($idInstance);

        $user = $instance->getUser();


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }


        $vhosts = array();
        foreach ($instance->getVhosts() as $v) {
            $vhosts[] = new \AppBundle\Soap\Entity\Vhosts($v->getId(), $v->getDateCrea(), $v->getName(), $v->getState(), $v->getInMaintenance());
        }
        $d = $instance->getDataCenter();
        $p = $instance->getProduct();
        $pr = $instance->getProductRenew();


        $options = array();

        // Puissance
        $puissance = $instance->getSizeInstance()->getProduct();
        $pricePuissance = $this->getProduct($username, $password, $puissance->getId(), $instance->getUser()->getId());
        $options[] = $puissance->getName() . ' (' . ($pricePuissance->minPriceHT ? $pricePuissance->minPriceHT : $pricePuissance->priceHT) . ' € HT/mois)';


        // Sauvegarde automatique :
        if ($instance->getSnapshopProfileInstance()->getId() != 2) {
            $save = $instance->getSnapshopProfileInstance()->getProduct();
            $priceSave = $this->getProduct($username, $password, $save->getId(), $instance->getUser()->getId());
            $options[] = $save->getName() . ' (' . (.3 * (10 + $instance->getDataDiskAdditionalSize()) * ($priceSave->minPriceHT ? $priceSave->minPriceHT : $priceSave->priceHT)) . ' € HT/mois)';
        }
        // Nombre de vhosts
        $nbreVhosts = $instance->getNumberMaxVhosts()->getProduct();
        $priceNbreVhosts = $this->getProduct($username, $password, $nbreVhosts->getId(), $instance->getUser()->getId());
        $options[] = $nbreVhosts->getName() . ' (' . ($priceNbreVhosts->minPriceHT ? $priceNbreVhosts->minPriceHT : $priceNbreVhosts->priceHT) . ' € HT/mois)';


        // On regarde si on a des parts en options.


        $part = $instance->getProductPartHdd();

        $features = $part->getFeatures();
        $gandiTotalDataDiskSize = $instance->getDataDiskAdditionalSize() + 10;
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $optionInstance = $productRepository->findOneByReference('parthdd');
        $objPart = $this->getProduct($username, $password, $optionInstance->getId(), $instance->getUser()->getId());
        $price = $objPart->minPriceHT ? $objPart->minPriceHT : $objPart->priceHT;
        $nbPart = ($gandiTotalDataDiskSize - $p->getFeatures()->tailleDisque) / $optionInstance->getFeatures()->part;
        if ($nbPart > 0) {
            $options[] = $optionInstance->getName() . ' : ' . $nbPart . ' parts (' . $price . '€ HT / mois et par part)';
        }
        // On compte le nombre de vhosts en maintenance.

        $nbVhosts = 0;
        foreach ($instance->getVhosts() as $vhost) {
            if ($vhost->getInMaintenance()) $nbVhosts++;
        }

        if ($nbVhosts > 0) {
            // On charge le produit dont la reference est simplehostingmaintenance
            $dMaintenance = $productRepository->findOneByReference('simplehostingmaintenance');
            $maint = $this->getProduct($username, $password, $dMaintenance->getId(), $instance->getUser()->getId());
            $priceMainteance = $maint->minPriceHT ? $maint->minPriceHT : $maint->priceHT;
            $options[] = "Assistance technique (" . $priceMainteance . "€ HT /mois et par site) : " . $nbVhosts;
        }
        $features = $p->getFeatures();
        $gandiTotalDataDiskSize = $instance->getDataDiskAdditionalSize() + 10;
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $optionInstance = $productRepository->findOneByReference('parthdd');


        return new \AppBundle\Soap\Entity\Instance(
            $instance->getId(),//$id,
            $instance->getCatalogName(),//$catalogName,
            $instance->getConsole(),//$console,
            $gandiTotalDataDiskSize - $features->tailleDisque,//$dataDiskAdditionalSize,
            ($gandiTotalDataDiskSize - $features->tailleDisque) / $optionInstance->getFeatures()->part,  // $quantityPartDataDiskAdditionalSize
            $gandiTotalDataDiskSize,//$dataDiskTotalSize,
            $instance->getDateEnd(),//$dateEnd,
            $instance->getDateEndCommitment(),//$dateEndCommitment,
            $instance->getDateStart(),//$dateStart,
            $instance->getFtpServer(),//$ftpServer,
            $instance->getGitServer(),//$gitServer,
            $instance->getName(),//$name,
            $instance->getNeedUpgrade(),//$needUpgrade,
            $instance->getUserFtp(),//$userFtp,
            $vhosts,//$vhosts,
            new \AppBundle\Soap\Entity\DataCenter($d->getId(), $d->getCountry(), $d->getDcCode(), $d->getIso(), $d->getName()),//$dataCenter,
            $this->getProduct($username, $password, $p->getid(), $instance->getUser()->getId()),//$product,
            ($instance->getSizeInstance() ? $instance->getSizeInstance()->getName() : null),// sizeInstance
            ($instance->getSnapshopProfileInstance() ? new SnapshotProfile($instance->getSnapshopProfileInstance()->getId(), $instance->getSnapshopProfileInstance()->getName(), null, $instance->getSnapshopProfileInstance()->getProduct()->getId()) : null),//$snapshopProfileInstance,
            ($instance->getTypeInstance() ? $instance->getTypeInstance()->getName() : null),//$typeInstance,
            $instance->getActive(),//$active
            $this->getProduct($username, $password, $pr->getid(), $instance->getUser()->getId()), //$productRenew
            $this->getProduct($username, $password, $optionInstance, $instance->getUser()->getId()), //productPartHdd
            $options,
            $this->getCustomer($username,$password,$instance->getUser()->getId()), // user
            $instance->getNumberMaxVhosts()->getValue(), $instance->getFreeDisk(), $instance->getUsedDisk(),$instance->getGestionConsole(),$instance->getEtatConsole()

        );


    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param string $tag
     * @return bool
     * @throws \SoapFault
     */
    public function addTagMaintenance($username, $password, $idUser, $tag)
    {
        $userApi = $this->login($username, $password);
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');


        $user = $userRepository->find($idUser);
        // Si Legrain : OK
        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) {

            $error = false;

        }
        if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        // On loade le tag, et on supprime
        $bug = new Bugzilla();
        $bug->setUser($user);
        $bug->setTag($tag);

        $this->em->persist($bug);
        $this->em->flush();
        return true;


    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @param string $tag
     * @return bool
     * @throws \SoapFault
     */
    public function delTagMaintenance($username, $password, $idUser, $tag)
    {
        $userApi = $this->login($username, $password);
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');


        $user = $userRepository->find($idUser);
        // Si Legrain : OK
        $error = true;
        if ($userApi->getRoles()->contains($roleLegrain)) {

            $error = false;

        }
        if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');

        // On loade le tag, et on supprime
        $bug = $bugzillaRepository->findOneBy(array('user' => $user, 'tag' => $tag));

        $this->em->remove($bug);
        $this->em->flush();
        return true;


    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return mixed
     * @throws \SoapFault
     */
    public function listTagsMaintenance($username, $password, $idUser)
    {
        // echo test
        $userApi = $this->login($username, $password);
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $user = $userRepository->find($idUser);


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        $tags = $bugzillaRepository->findByUser($user);

        $return = array();
        foreach ($tags as $t) $return[] = $t->getTag();

        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @return string
     * @throws \SoapFault
     */
    public function listInterventions($username, $password, $domain)
    {
        $userApi = $this->login($username, $password);
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $listBugBugzillaRepository = $this->em->getRepository('AppBundle:ListBugBugzilla');
        $curl = $this->curlBugzilla;


        $ndd = $nddRepository->findOneByName($domain);
        if (!$ndd) throw new \SoapFault('server', 'Ndd inconnu');

        // Si le propriaitaire du domaine a bien le tag "Sites Web Clients"
        $tagBugzilla = $bugzillaRepository->findOneBy(array('user' => $ndd->getUser(), 'tag' => 'Sites Web Clients'));
        if (!$tagBugzilla) throw new \SoapFault('server', 'Le tag Site Web Clients est introuvable pour cet utilisateur');
        // Si le domaine est bien dans la liste des composants de "Sites Web Clients"
        if (!in_array($ndd->getName(), $curl->listComponents('Sites%20Web%20Clients'))) throw new \SoapFault('server', 'Le tag Site Web Clients est introuvable pour cet utilisateur');

        // récupération de la liste des bugs de bugzilla
        $listBugzilla = $curl->listBugs('Sites%20Web%20Clients', $ndd->getName());
        $result = array();
        foreach ($listBugzilla as $l) {
            $bug = $listBugBugzillaRepository->findOneByIdBug($l->id);
            if ($bug == null) {
                $bug = new ListBugBugzilla();
                $bug->setIdBug($l->id);
                $bug->setNdd($ndd);
                $bug->setIsRead(false);
                $bug->setDateLastUpdate(new \DateTime($l->last_change_time));
            } else {
                $lastDate = $bug->getDateLastUpdate();
                $newDate = new \DateTime($l->last_change_time);
                if ($lastDate->format('YmdHis') != $newDate->format('YmdHis')) {
                    $bug->setIsRead('false');
                }
                $bug->setDateLastUpdate($newDate);
            }
            $this->em->persist($bug);
            $tmp = array(
                'name' => $l->summary,
                'creation_time' => $l->creation_time,
                'status' => $l->status,
                'last_change_time' => $l->last_change_time,
                'isRead' => $bug->getIsRead(),
                'id' => $bug->getId()
            );
            $result[] = $tmp;
            // Récupération des infos à afficher
        }
        $this->em->flush();

        return json_encode($result);


    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idBug
     * @return string
     * @throws \SoapFault
     */
    public function detailIntervention($username, $password, $idBug)
    {
        $userApi = $this->login($username, $password);
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $bugzillaRepository = $this->em->getRepository('AppBundle:Bugzilla');
        $listBugBugzillaRepository = $this->em->getRepository('AppBundle:ListBugBugzilla');
        $curl = $this->curlBugzilla;


        $bug = $listBugBugzillaRepository->find($idBug);
        if (!$bug) throw new \SoapFault('server', 'Intervention inconnue');
        $ndd = $nddRepository->findOneByName($bug->getNdd()->getName());
        if (!$ndd) throw new \SoapFault('server', 'Ndd inconnu');

        // Si le propriaitaire du domaine a bien le tag "Sites Web Clients"
        $tagBugzilla = $bugzillaRepository->findOneBy(array('user' => $ndd->getUser(), 'tag' => 'Sites Web Clients'));
        if (!$tagBugzilla) throw new \SoapFault('server', 'Le tag Site Web Clients est introuvable pour cet utilisateur');
        // Si le domaine est bien dans la liste des composants de "Sites Web Clients"
        if (!in_array($ndd->getName(), $curl->listComponents('Sites%20Web%20Clients'))) throw new \SoapFault('server', 'Le tag Site Web Clients est introuvable pour cet utilisateur');

        // Récupération des commentaires pour le bug.
        $list = $curl->getCommentsBugs($bug->getIdBug());
        $result = array();
        foreach ($list as $l) {
            $result[] = array(
                'text' => $l->text,
                'time' => $l->time,
            );
        }
        $bug->setIsRead(true);
        $this->em->persist($bug);
        $this->em->flush();
        return json_encode($result);
    }

    /**
     * @param string $username
     * @param string $password
     * @return string
     * @throws \SoapFault
     */
    public function getChangelog($username, $password)
    {

        $userApi = $this->login($username, $password);

        $roleRepository = $this->em->getRepository('AppBundle:Roles');

        $roleAgenceWeb = $roleRepository->findOneByName('ROLE_AGENCE');
        $roleUtilisateurAgence = $roleRepository->findOneByName('ROLE_UTILISATEUR_AGENCE');
        $return = array();
        if ($userApi->getRoles()->contains($roleAgenceWeb)) {
            $idChangelog = $this->id_changelog_gwi;
        } elseif ($userApi->getRoles()->contains($roleUtilisateurAgence)) {
            $idChangelog = $this->id_changelog_gwi_utilisateur_agence_web;
        } else {
            $idChangelog = null;
        }

        if ($idChangelog != null) {
            try {
                //   $client = $this->container->get('besimple.soap.client.changelogApi');

                $client = new \Zend\Soap\Client($this->wsdl_changelog,
                    array('compression' =>
                        SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE));


                $result = $client->get($idChangelog);

                $return = $result;

            } catch (\SoapFault $e) {
                throw new \SoapFault('server', $e->getMessage());
            }
        }

        return json_encode($return);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idinstance
     * @return \AppBundle\Soap\Entity\InstanceSnapshot[]
     * @throws \SoapFault
     */
    public function listInstanceSnapshots($username, $password, $idinstance)
    {
        $userApi = $this->login($username, $password);
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');
        $userRepository = $this->em->getRepository('AppBundle:User');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $instance = $instanceRepository->find($idinstance);

        $user = $instance->getUser();


        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        // Appel du client gandi
        // Appel Gandi

        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $list = $gandiApi->paasSnapshotList($connect, $instance->getIdGandi());

        $return = array();
        foreach ($list as $l) {
            $date = new \DateTime(date('Y-m-d H:i:s', $l['date_created']->timestamp));
            $return[] = new \AppBundle\Soap\Entity\InstanceSnapshot($date, $l['name'], $l["size"], $l['type']);
        }
        return $return;
    }


    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\TiersPourTVA[]
     * @throws \SoapFault
     */
    public function listTiersPourTVA($username, $password)
    {
        $userApi = $this->login($username, $password);
        $tiersPourTvaRepository = $this->em->getRepository('AppBundle:TiersPourTVA');

        $return = array();
        //$tiersPourTva=null;
        foreach ($tiersPourTvaRepository->findAll() as $tiersPourTva) {
            $return[] = new TiersPourTVA($tiersPourTva->getId(), $tiersPourTva->getName());
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\CGU[]
     * @throws \SoapFault
     */
    public function listAllCgu($username, $password)
    {
        $userApi = $this->login($username, $password);
        $cguRepository = $this->em->getRepository('AppBundle:CGU');

        $return = array();
        //$tiersPourTva=null;
        foreach ($cguRepository->findAll() as $cgu) {
            $return[] = new CGU($cgu->getId(), $cgu->getName(), $cgu->getContent(), $cgu->getUrl());
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $content
     * @param string $url
     * @return bool
     * @throws \SoapFault
     */
    public function addCgu($username, $password, $name, $content, $url)
    {
        $userApi = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        if (!$userApi->getRoles()->contains($roleLegrain)) throw new \SoapFault('error', 'Accès interdit');
        try {
            $cgu = new \AppBundle\Entity\CGU();
            $cgu->setName($name);
            $cgu->setContent($content);
            $cgu->seturl($url);

            $this->em->persist($cgu);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            throw new \SoapFault('error', 'Cette url est déjà présente dans la base de données');
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idcgu
     * @param string $name
     * @param string $content
     * @param string $url
     * @return bool
     * @throws \SoapFault
     */
    public function updateCgu($username, $password, $idcgu, $name, $content, $url)
    {
        $userApi = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        if (!$userApi->getRoles()->contains($roleLegrain)) throw new \SoapFault('error', 'Accès interdit');
        $cguRepository = $this->em->getRepository('AppBundle:CGU');
        $cgu = $cguRepository->find($idcgu);
        if ($cgu == null) throw new \SoapFault('Cette CGU n\'existe pas ou plus');
        try {

            $cgu->setName($name);
            $cgu->setContent($content);
            $cgu->seturl($url);

            $this->em->persist($cgu);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            throw new \SoapFault('error', 'Cette url est déjà présente dans la base de données');
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idcgu
     * @return \AppBundle\Soap\Entity\CGU
     * @throws \SoapFault
     */
    public function getCgu($username, $password, $idcgu)
    {
        $userApi = $this->login($username, $password);

        $cguRepository = $this->em->getRepository('AppBundle:CGU');
        $cgu = $cguRepository->find($idcgu);
        if ($cgu == null) throw new \SoapFault('Cette CGU n\'existe pas ou plus');

        return new CGU($cgu->getId(), $cgu->getName(), $cgu->getContent(), $cgu->getUrl());

    }

    /**
     * @param string $username
     * @param string $password
     * @param string $url
     * @return \AppBundle\Soap\Entity\CGU
     * @throws \SoapFault
     */
    public function getCguByUrl($username, $password, $url)
    {
        $userApi = $this->login($username, $password);

        $cguRepository = $this->em->getRepository('AppBundle:CGU');
        $cgu = $cguRepository->findOneByUrl($url);
        if ($cgu == null) throw new \SoapFault('Cette CGU n\'existe pas ou plus');

        return new CGU($cgu->getId(), $cgu->getName(), $cgu->getContent(), $cgu->getUrl());

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idcgu
     * @return bool
     * @throws \SoapFault
     */
    public function removeCgu($username, $password, $idcgu)
    {
        $userApi = $this->login($username, $password);
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        if (!$userApi->getRoles()->contains($roleLegrain)) throw new \SoapFault('error', 'Accès interdit');
        $cguRepository = $this->em->getRepository('AppBundle:CGU');
        $cgu = $cguRepository->find($idcgu);
        if ($cgu == null) throw new \SoapFault('Cette CGU n\'existe pas ou plus');


        $this->em->remove($cgu);
        $this->em->flush();
        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idUser
     * @return bool
     * @throws \SoapFault
     */
    public function isPossibleToPayByCard($username, $password, $idUser)
    {
        $userApi = $this->login($username, $password);
        $userConnected = $this->em->getRepository('AppBundle:User')->find($idUser);
        if ($userConnected->getParent() == null) return true;
        else {
            $return = false;

            if ($userConnected->getAgency()->getStripeKey() != null) {
                $return = true;
            } elseif ($userConnected->getAgency()->getFacturationBylegrain()) {
                $return = true;
            }

            return $return;
        }
    }

    private function returnListProductsHosting($list)
    {

        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
        $priceListApplicationDefault = $priceListRepository->findOneByIsApplicationDefault(true);
        $return = array();
        foreach ($list as $item) {

            $priceListLine = $priceListLineRepository->findOneBy(array('product' => $item->getProduct(), 'priceList' => $priceListApplicationDefault));

            // $agency = new \AppBundle\Soap\Entity\Agency($item->getAgency()->getId(), $item->getAgency()->getName(), $item->getAgency()->getSiret(), $item->getAgency()->getAddress1(), $item->getAgency()->getAddress2(), $item->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($item->getAgency()->getCity()->getId(), $item->getAgency()->getCity()->getName(), $item->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($item->getAgency()->getZipCode()->getId(), $item->getAgency()->getZipCode()->getName()), $item->getAgency()->getPhone(), $item->getAgency()->getEmail(), $item->getAgency()->getWebsite(),$item->getAgency()->getFacturationBylegrain(),$item->getAgency()->getInfosCheque(),$item->getAgency()->getInfosVirement());
            $agency = $this->_getAgency($item->getAgency());
            $product = new \AppBundle\Soap\Entity\Product(

                $item->getProduct()->getId(),
                $item->getProduct()->getName(),
                $item->getProduct()->getReference(),
                $item->getProduct()->getCodeLgr(),
                $item->getProduct()->getShortDescription(),
                $item->getProduct()->getLongDescription(),
                $item->getProduct()->getMinPeriod(),
                null,
                null,
                null,
                $priceListLine->getTvaRate()->getPercent(),// Taux tva
                null, null, null, null, null,
                $item->getProduct()->getActive(), null
            );

            $instance = new \AppBundle\Soap\Entity\Instance(
                $item->getInstance()->getId(),//$id,
                $item->getInstance()->getCatalogName(),//$catalogName,
                $item->getInstance()->getConsole(),//$console,
                null,//$dataDiskAdditionalSize,
                null,  // $quantityPartDataDiskAdditionalSize
                null,//$dataDiskTotalSize,
                $item->getInstance()->getDateEnd(),//$dateEnd,
                $item->getInstance()->getDateEndCommitment(),//$dateEndCommitment,
                $item->getInstance()->getDateStart(),//$dateStart,
                $item->getInstance()->getFtpServer(),//$ftpServer,
                $item->getInstance()->getGitServer(),//$gitServer,
                $item->getInstance()->getName(),//$name,
                $item->getInstance()->getNeedUpgrade(),//$needUpgrade,
                $item->getInstance()->getUserFtp(),//$userFtp,
                null,//$vhosts,
                null,//$dataCenter,
                null,//$product,
                ($item->getInstance()->getSizeInstance() ? $item->getInstance()->getSizeInstance()->getName() : null),// sizeInstance
                ($item->getInstance()->getSnapshopProfileInstance() ? new SnapshotProfile($item->getInstance()->getSnapshopProfileInstance()->getId(), $item->getInstance()->getSnapshopProfileInstance()->getName(), null, $item->getInstance()->getSnapshopProfileInstance()->getProduct()->getId()) : null),//$snapshopProfileInstance,
                ($item->getInstance()->getTypeInstance() ? $item->getInstance()->getTypeInstance()->getName() : null),//$typeInstance,
                $item->getInstance()->getActive(),//$active
                null, //$productRenew
                null, //productPartHdd
                null, null, $item->getInstance()->getNumberMaxVhosts()->getValue(), $item->getInstance()->getFreeDisk(), $item->getInstance()->getUsedDisk()
            );

            $return[] = new \AppBundle\Soap\Entity\ProductHosting(
                $item->getId(),
                $item->getName(),
                $item->getPriceHt(),
                $item->getBookableByCustomer(),
                $item->getRenewByCustomer(),
                $item->getDetail(),
                $item->getFeatures(),
                $product,
                $instance,
                $agency
            );
        }
        return $return;
    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\ProductHosting[]
     * @throws \SoapFault
     */
    public function listProductsHosting($username, $password)
    {
        $userApi = $this->login($username, $password);

        if (!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE')))
            throw new \SoapFault('forbidden', 'Accès interdit');


        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');
        $list = $productHostingRepository->findByAgency($userApi->getAgency());
        return $this->returnListProductsHosting($list);


    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @return bool
     * @throws \SoapFault
     */
    public function removeProductsHosting($username, $password, $idProduct)
    {
        $userApi = $this->login($username, $password);

        if (!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE')))
            throw new \SoapFault('forbidden', 'Accès interdit');
        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');
        $item = $productHostingRepository->find($idProduct);

        if ($item == null) throw new \SoapFault('error', 'Le produit n\'existe pas ou plus');
        if ($item->getAgency()->getId() != $userApi->getAgency()->getId()) throw new \SoapFault('forbidden', 'Accès interdit');

        $this->em->remove($item);
        $this->em->flush();

        return true;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @param string $newName
     * @param int $idNewInstance
     * @param bool $newIsBookableByCustomer
     * @param bool $newRenewByCustomer
     * @param float $newPriceHt
     * @param string $newDetail
     *
     * @return bool
     * @throws \SoapFault
     */
    public function updateProductsHosting($username, $password, $idProduct, $newName, $idNewInstance, $newIsBookableByCustomer, $newRenewByCustomer, $newPriceHt, $newDetail)
    {
        $userApi = $this->login($username, $password);

        if (!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE')))
            throw new \SoapFault('forbidden', 'Accès interdit');

        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');

        $instance = $instanceRepository->find($idNewInstance);


        $item = $productHostingRepository->find($idProduct);

        if ($item == null) throw new \SoapFault('error', 'Le produit n\'existe pas ou plus');
        if ($item->getAgency()->getId() != $userApi->getAgency()->getId()) throw new \SoapFault('forbidden', 'Accès interdit');

        if ($instance->getUser()->getId() != $userApi->getId()) throw new \SoapFault('forbidden', 'Accès interdit');

        $item->setName($newName);
        $item->setInstance($instance);
        $item->setBookableByCustomer($newIsBookableByCustomer);
        $item->setRenewByCustomer($newRenewByCustomer);
        $item->setPriceHt($newPriceHt);
        $item->setDetail($newDetail);

        $this->em->persist($item);
        $this->em->flush();

        return true;

    }

    /**
     * @param string $urlApp
     * @return \AppBundle\Soap\Entity\ProductHosting[]
     * @return bool
     */
    public function publicPricesProductsHebergementMutualisable($urlApp)
    {
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $agency = $agencyRepository->findOneByUrlApp($urlApp);
        if ($agency == null || ($agency->getId() != 1 && $agency->getFacturationByLegrain())) throw new \SoapFault('e', 'Accès impossible');

        try {
            $list = $this->em->getRepository('AppBundle:ProductHosting')->listProductsHeberDispoPerAgency($agency);
            return $this->returnListProductsHosting($list);
            //  return count($list)>0?true:false;
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\ProductHosting[]
     * @return bool
     */
    public function publicPricesProductsHebergementMutualisablePerUser($username, $password)
    {
        $userApi = $this->login($username, $password);
        $agency = $userApi->getAgency();
        if ($agency == null || ($agency->getId() != 1 && $agency->getFacturationByLegrain())) throw new \SoapFault('e', 'Accès impossible');
        try {
            if ($userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE'))) {
                $list = $this->em->getRepository('AppBundle:ProductHosting')->listProductsHeberDispoPerGestionnaire($agency);
                return $this->returnListProductsHosting($list);

            } else {
                $list = $this->em->getRepository('AppBundle:ProductHosting')->listProductsHeberDispoPerAgency($agency);
                return $this->returnListProductsHosting($list);
            }
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }

    }

    /**
     * @param string $urlApp
     * @return bool
     */
    public function proposeHerbergementMutualise($urlApp)
    {
        // Si ndd pas trouvé, on retourne faux
        $agencyRepository = $this->em->getRepository('AppBundle:Agency');
        $agency = $agencyRepository->findOneByUrlApp($urlApp);
        if ($agency == null || ($agency->getId() != 1 && $agency->getFacturationByLegrain())) return false;

        try {
            $list = $this->em->getRepository('AppBundle:ProductHosting')->listProductsHeberDispoPerAgency($agency);
            return count($list) > 0 ? true : false;
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }

    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \SoapFault
     */
    public function proposeHerbergementMutualisePerUser($username, $password)
    {
        $userApi = $this->login($username, $password);
        if ($userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE'))) {
            $agency = $userApi->getAgency();
            if ($agency == null || ($agency->getId() != 1 && $agency->getFacturationByLegrain())) return false;
            try {
                $list = $this->em->getRepository('AppBundle:ProductHosting')->listProductsHeberDispoPerGestionnaire($agency);
                return count($list) > 0 ? true : false;
            } catch (\Exception $e) {
                throw new \SoapFault('e', $e->getMessage());
            }
        } else {
            return $this->proposeHerbergementMutualise($userApi->getAgency()->getUrlApp());
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     * @param int $idInstance
     * @param bool $isBookableByCustomer
     * @param bool $renewByCustomer
     * @param float $priceHt
     * @param string $detail
     * @return \AppBundle\Soap\Entity\ProductHosting
     * @throws \SoapFault
     */
    public function createProductsHosting($username, $password, $name, $idInstance, $isBookableByCustomer, $renewByCustomer, $priceHt, $newDetail)
    {
        $userApi = $this->login($username, $password);

        if (!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE')))
            throw new \SoapFault('forbidden', 'Accès interdit');

        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');
        $instanceRepository = $this->em->getRepository('AppBundle:Instance');

        $instance = $instanceRepository->find($idInstance);

        $tvaRate = $this->em->getRepository('AppBundle:TvaRate')->find(1);

        $product = $this->em->getRepository('AppBundle:Product')->findOneByReference('produit_generique_instance_mutualisable');

        $item = new ProductHosting();

        if ($instance->getUser()->getId() != $userApi->getId()) throw new \SoapFault('forbidden', 'Accès interdit');

        $item->setAgency($userApi->getAgency());
        $item->setName($name);
        $item->setInstance($instance);
        $item->setBookableByCustomer($isBookableByCustomer);
        $item->setRenewByCustomer($renewByCustomer);
        $item->setPriceHt($priceHt);
        $item->setDetail($newDetail);


        $item->setTvaRate($tvaRate);
        $item->setProduct($product);
        $this->em->persist($item);
        $this->em->flush();

        return $this->getProductsHosting($username, $password, $item->getId());

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $idProduct
     * @return \AppBundle\Soap\Entity\ProductHosting
     * @throws \SoapFault
     */
    public function getProductsHosting($username, $password, $idProduct)
    {
        $userApi = $this->login($username, $password);
        $priceListRepository = $this->em->getRepository('AppBundle:PriceList');
        $priceListLineRepository = $this->em->getRepository('AppBundle:PriceListLine');
        $priceListApplicationDefault = $priceListRepository->findOneByIsApplicationDefault(true);
        // if(!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_AGENCE')))
        //   throw new \SoapFault('forbidden','Accès interdit');

        $productHostingRepository = $this->em->getRepository('AppBundle:ProductHosting');

        $item = $productHostingRepository->find($idProduct);

        if ($item == null) throw new \SoapFault('error', 'Le produit n\'existe pas ou plus');
        // Si different de legrain
        if (!$userApi->getRoles()->contains($this->em->getRepository('AppBundle:Roles')->findOneByName('ROLE_LEGRAIN'))) {
            if ($item->getAgency()->getId() != $userApi->getAgency()->getId() && $userApi->getParent() != null) throw new \SoapFault('forbidden', 'Accès interdit');
        }
        $priceListLine = $priceListLineRepository->findOneBy(array('product' => $item->getProduct(), 'priceList' => $priceListApplicationDefault));

        //$agency = new \AppBundle\Soap\Entity\Agency($item->getAgency()->getId(), $item->getAgency()->getName(), $item->getAgency()->getSiret(), $item->getAgency()->getAddress1(), $item->getAgency()->getAddress2(), $item->getAgency()->getAddress3(), new \AppBundle\Soap\Entity\City($item->getAgency()->getCity()->getId(), $item->getAgency()->getCity()->getName(), $item->getAgency()->getCity()->getCodeInsee()), new \AppBundle\Soap\Entity\ZipCode($item->getAgency()->getZipCode()->getId(), $item->getAgency()->getZipCode()->getName()), $item->getAgency()->getPhone(), $item->getAgency()->getEmail(), $item->getAgency()->getWebsite(),$item->getAgency()->getFacturationBylegrain(),$item->getAgency()->getInfosCheque(),$item->getAgency()->getInfosVirement());
        $agency = $this->_getAgency($item->getAgency());
        $product = new \AppBundle\Soap\Entity\Product(

            $item->getProduct()->getId(),
            $item->getProduct()->getName(),
            $item->getProduct()->getReference(),
            $item->getProduct()->getCodeLgr(),
            $item->getProduct()->getShortDescription(),
            $item->getProduct()->getLongDescription(),
            $item->getProduct()->getMinPeriod(),
            null,
            null,
            null,
            $priceListLine->getTvaRate()->getPercent(),// Taux tva
            null, null, null, null, null,
            $item->getProduct()->getActive(), null
        );

        $instance = new \AppBundle\Soap\Entity\Instance(
            $item->getInstance()->getId(),//$id,
            $item->getInstance()->getCatalogName(),//$catalogName,
            $item->getInstance()->getConsole(),//$console,
            null,//$dataDiskAdditionalSize,
            null,  // $quantityPartDataDiskAdditionalSize
            null,//$dataDiskTotalSize,
            $item->getInstance()->getDateEnd(),//$dateEnd,
            $item->getInstance()->getDateEndCommitment(),//$dateEndCommitment,
            $item->getInstance()->getDateStart(),//$dateStart,
            $item->getInstance()->getFtpServer(),//$ftpServer,
            $item->getInstance()->getGitServer(),//$gitServer,
            $item->getInstance()->getName(),//$name,
            $item->getInstance()->getNeedUpgrade(),//$needUpgrade,
            $item->getInstance()->getUserFtp(),//$userFtp,
            null,//$vhosts,
            null,//$dataCenter,
            null,//$product,
            ($item->getInstance()->getSizeInstance() ? $item->getInstance()->getSizeInstance()->getName() : null),// sizeInstance
            ($item->getInstance()->getSnapshopProfileInstance() ? new SnapshotProfile($item->getInstance()->getSnapshopProfileInstance()->getId(), $item->getInstance()->getSnapshopProfileInstance()->getName(), null, $item->getInstance()->getSnapshopProfileInstance()->getProduct()->getId()) : null),//$snapshopProfileInstance,
            ($item->getInstance()->getTypeInstance() ? $item->getInstance()->getTypeInstance()->getName() : null),//$typeInstance,
            $item->getInstance()->getActive(),//$active
            null, //$productRenew
            null, //productPartHdd
            null, null, $item->getInstance()->getNumberMaxVhosts()->getValue(), $item->getInstance()->getFreeDisk(), $item->getInstance()->getUsedDisk()
        );

        $return = new \AppBundle\Soap\Entity\ProductHosting(
            $item->getId(),
            $item->getName(),
            $item->getPriceHt(),
            $item->getBookableByCustomer(),
            $item->getRenewByCustomer(),
            $item->getDetail(),
            $item->getFeatures(),
            $product,
            $instance,
            $agency
        );

        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\Hosting[]
     * @throws \SoapFault
     */
    public function listMyHebergementsMutualises($username, $password)
    {
        $userApi = $this->login($username, $password);
        // On sélectionne les hébergements du demandeur
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');

        $list = $hostingRepository->findBy(array('user' => $userApi));

        $return = array();
        foreach ($list as $item) {
            // if($item->getProductHosting()->getAgency()->getId()==$userApi->getAgency()->getId()) {
            $user = $this->getCustomer($username, $password, $item->getUser()->getId());
            $productHosting = $this->getProductsHosting($username, $password, $item->getProductHosting()->getId());
            $return[] = new \AppBundle\Soap\Entity\Hosting($item->getId(), $item->getVhost(), $item->getDateEnding(), $productHosting, $user);
            //}
        }
        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @param int $iduser
     * @return \AppBundle\Soap\Entity\Hosting[]
     * @throws \SoapFault
     */
    public function listHebergementsMutualises($username, $password, $iduser)
    {
        $userApi = $this->login($username, $password);
        // On sélectionne les hébergements du demandeur
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');

        $userRepository = $this->em->getRepository('AppBundle:User');
        $user = $userRepository->find($iduser);

        if (!$user) throw new \SoapFault('FORBIDDEN', 'Utilisateur inconnu');
        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        // Si Legrain : OK
        // Si pas Legrain on regarde si c'est lui ou un de ses fils ou un de ses fils
        if (!$userApi->getRoles()->contains($roleLegrain)) {

            $error = true;
            if ($userApi->getId() == $user->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $user->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }


        $list = $hostingRepository->findBy(array('user' => $iduser));

        $return = array();
        foreach ($list as $item) {
            $user = $this->getCustomer($username, $password, $item->getUser()->getId());
            $productHosting = $this->getProductsHosting($username, $password, $item->getProductHosting()->getId());
            $return[] = new \AppBundle\Soap\Entity\Hosting($item->getId(), $item->getVhost(), $item->getDateEnding(), $productHosting, $user);
        }
        return $return;

    }

    /**
     * @param string $username
     * @param string $password
     * @return \AppBundle\Soap\Entity\Hosting[]
     * @throws \SoapFault
     */
    public function listAllHebergementsMutualises($username, $password)
    {
        try {
            $userApi = $this->login($username, $password);
            // On sélectionne les hébergements du demandeur
            $hostingRepository = $this->em->getRepository('AppBundle:Hosting');
            $roleRepository = $this->em->getRepository('AppBundle:Roles');

            if ($userApi->getRoles()->contains($roleRepository->findOneByName('ROLE_LEGRAIN'))) {
                $list = $hostingRepository->findAllCustomersLegrain($userApi->getAgency());

            } elseif ($userApi->getRoles()->contains($roleRepository->findOneByName('ROLE_AGENCE'))) {

                $list = $hostingRepository->findByAgency($userApi->getAgency());
            } else {
                throw new \SoapFault('e', 'Accès interdit');
            }
            $return = array();
            foreach ($list as $item) {
                $user = $this->getCustomer($username, $password, $item->getUser()->getId());
                $productHosting = $this->getProductsHosting($username, $password, $item->getProductHosting()->getId());
                $return[] = new \AppBundle\Soap\Entity\Hosting($item->getId(), $item->getVhost(), $item->getDateEnding(), $productHosting, $user);
            }
            return $return;
        } catch (\Exception $e) {
            throw new \SoapFault('e', $e->getMessage());
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param int $idHosting
     * @return \AppBundle\Soap\Entity\Hosting
     * @throws \SoapFault
     */
    public function getHosting($username, $password, $idHosting)
    {
        $userApi = $this->login($username, $password);
        // On sélectionne les hébergements du demandeur
        $hostingRepository = $this->em->getRepository('AppBundle:Hosting');

        $item = $hostingRepository->find($idHosting);
        $user = $this->getCustomer($username, $password, $item->getUser()->getId());
        $productHosting = $this->getProductsHosting($username, $password, $item->getProductHosting()->getId());
        return new \AppBundle\Soap\Entity\Hosting($item->getId(), $item->getVhost(), $item->getDateEnding(), $productHosting, $user);


    }

    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @return \AppBundle\Soap\Entity\DomainZone
     * @throws \SoapFault
     */
    public function listZoneVersionDNS($username, $password, $domain)
    {
        $userApi = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        if (!$userApi->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $userApi->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if (!$ndd) throw new \SoapFault('FORBIDDEN', 'ndd inconnu');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $infosDomain = $gandiApi->infosDomain($connect, $domain);
        $zoneId = $infosDomain->zoneId;

        $info = $gandiApi->domainZoneInfo($connect, $zoneId);


        if($info['public']){
            $new = $gandiApi->domainZoneClone($connect,$zoneId);
            $zoneId = $new['id'];
            $gandiApi->domainZoneSet($connect,$domain,$zoneId);
        }

        $versions = array();
        foreach ($info['versions'] as $item) {

            $records = $gandiApi->domainZoneRecordList($connect, $zoneId, $item);
            $tmp = array();
            foreach ($records as $record) {
                $tmp[] = new \AppBundle\Soap\Entity\ZoneRecordReturn($record['name'], $record['ttl'], $record['type'], $record['value']);
            }

            $versions[] = new \AppBundle\Soap\Entity\DomainZoneVersion($item, $tmp);
        }


        return new \AppBundle\Soap\Entity\DomainZone($info['version'], $versions);


    }


    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $records
     * @return int $idNewVersion
     * @throws \SoapFault
     */
    public function saveZoneDns($username, $password, $domain,$records)
    {
        $userApi = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        if (!$userApi->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $userApi->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if (!$ndd) throw new \SoapFault('FORBIDDEN', 'ndd inconnu');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);


        try {
            $infosDomain = $gandiApi->infosDomain($connect, $domain);
            $zoneId = $infosDomain->zoneId;
            // Creer une nouvelle version de zone
            $newNumVersion = $gandiApi->domainZoneVersionNew($connect, $zoneId);

            // Ajouter les nouveaux enregistrements

            $gandiApi->domainZoneSetRecordAction($connect, $zoneId, $newNumVersion, $records);
        }catch (\Exception $e){
            $gandiApi->domainZoneVersionDelete($connect,$zoneId ,$newNumVersion );
            throw new \SoapFault('e',$e->getMessage());
            // throw new \SoapFault('e',$e->getCode());
        }

        return $newNumVersion;
    }


    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param int $idVersion
     * @return boolean
     * @throws \SoapFault
     */
    public function deleteVersionZoneDns($username, $password, $domain,$idVersion)
    {
        $userApi = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        if (!$userApi->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $userApi->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if (!$ndd) throw new \SoapFault('FORBIDDEN', 'ndd inconnu');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $infosDomain = $gandiApi->infosDomain($connect, $domain);
        $zoneId = $infosDomain->zoneId;

        $gandiApi->domainZoneVersionDelete($connect,$zoneId,$idVersion);
        return true;
    }
    /**
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param int $idVersion
     * @return boolean
     * @throws \SoapFault
     */
    public function useVersionZoneDns($username, $password, $domain,$idVersion)
    {
        $userApi = $this->login($username, $password);


        $roleRepository = $this->em->getRepository('AppBundle:Roles');
        $roleLegrain = $roleRepository->findOneByName('ROLE_LEGRAIN');

        $nddRepository = $this->em->getRepository('AppBundle:Ndd');
        $ndd = $nddRepository->findOneByName($domain);

        if (!$userApi->getRoles()->contains($roleLegrain) && $ndd) {
            $error = true;
            if ($ndd->getUser()->getId() == $userApi->getId()) {
                $error = false;
            } else {
                foreach ($userApi->getChildren() as $child) {
                    if ($child->getId() == $ndd->getUser()->getId()) {
                        $error = false;
                        break;
                    }

                }
            }
            if ($error) throw new \SoapFault('FORBIDDEN', 'Accès refusé');
        }

        if (!$ndd) throw new \SoapFault('FORBIDDEN', 'ndd inconnu');
        $gandiApi = new \GandiBundle\Controller\GandiController();

        $usrGandi = 'hohloobeen1quaez7eis8eiBaiNgeita';
        $passwordGandi = 'cooBeeNgeijaerie9aibae0ohxootee5';

        $connect = new \GandiBundle\Entity\Connect($usrGandi, $passwordGandi);

        $infosDomain = $gandiApi->infosDomain($connect, $domain);
        $zoneId = $infosDomain->zoneId;

        $gandiApi->domainZoneVersionSet($connect, $zoneId, $idVersion);
        $gandiApi->domainZoneSet($connect, $domain, $zoneId);
        return true;

    }
}

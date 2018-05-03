<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
$EW_RELATIVE_PATH = "";
?>
<?php include_once $EW_RELATIVE_PATH . "ewcfg11.php" ?>
<?php include_once $EW_RELATIVE_PATH . "adodb5/adodb.inc.php" ?>
<?php include_once $EW_RELATIVE_PATH . "phpfn11.php" ?>
<?php include_once $EW_RELATIVE_PATH . "utenti_semia_info.php" ?>
<?php include_once $EW_RELATIVE_PATH . "userfn11.php" ?>
<?php

//
// Page class
//

$default = NULL; // Initialize page object first

class cdefault {

	// Page ID
	var $PageID = 'default';

	// Project ID
	var $ProjectID = "{66F59708-F0EF-4AAB-B4AB-E1A962BE0D3E}";

	// Page object name
	var $PageObjName = 'default';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-info ewInfo\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-danger ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div>";
	}
	var $Token = "";
	var $CheckToken = EW_CHECK_TOKEN;
	var $CheckTokenFn = "ew_CheckToken";
	var $CreateTokenFn = "ew_CreateToken";

	// Valid Post
	function ValidPost() {
		if (!$this->CheckToken || !ew_IsHttpPost())
			return TRUE;
		if (!isset($_POST[EW_TOKEN_NAME]))
			return FALSE;
		$fn = $this->CheckTokenFn;
		if (is_callable($fn))
			return $fn($_POST[EW_TOKEN_NAME]);
		return FALSE;
	}

	// Create Token
	function CreateToken() {
		global $gsToken;
		if ($this->CheckToken) {
			$fn = $this->CreateTokenFn;
			if ($this->Token == "" && is_callable($fn)) // Create token
				$this->Token = $fn();
			$gsToken = $this->Token; // Save to global variable
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// User table object (utenti_semia)
		if (!isset($GLOBALS["UserTable"])) $GLOBALS["UserTable"] = new cutenti_semia();

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'default', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsCustomExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Security
		$Security = new cAdvancedSecurity();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();

		// Check token
		if (!$this->ValidPost()) {
			echo $Language->Phrase("InvalidPostRequest");
			$this->Page_Terminate();
			exit();
		}

		// Create Token
		$this->CreateToken();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn, $gsExportFile, $gTmpImages;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Export
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}

	//
	// Page main
	//
	function Page_Main() {
		global $Security, $Language;
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		$Security->LoadUserLevel(); // Load User Level
		if ($Security->AllowList(CurrentProjectID() . 'dss-semia.php'))
		$this->Page_Terminate("dss-semia.php"); // Exit and go to default page
		if ($Security->AllowList(CurrentProjectID() . 'soci'))
			$this->Page_Terminate("soci_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'dati_mosca'))
			$this->Page_Terminate("dati_mosca_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'meteo-stat-cerca.php'))
			$this->Page_Terminate("meteo-stat-cerca.php");
		if ($Security->AllowList(CurrentProjectID() . 'meteo-stat-risultati.php'))
			$this->Page_Terminate("meteo-stat-risultati.php");
		if ($Security->AllowList(CurrentProjectID() . 'utenti_semia'))
			$this->Page_Terminate("utenti_semia_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'modelli-mosca.php'))
			$this->Page_Terminate("modelli-mosca.php");
		if ($Security->AllowList(CurrentProjectID() . 'modelli-mosca-stazione.php'))
			$this->Page_Terminate("modelli-mosca-stazione.php");
		if ($Security->AllowList(CurrentProjectID() . 'meteo.php'))
			$this->Page_Terminate("meteo.php");
		if ($Security->AllowList(CurrentProjectID() . 'monitoraggio-mosca.php'))
			$this->Page_Terminate("monitoraggio-mosca.php");
		if ($Security->AllowList(CurrentProjectID() . 'meteo-stazione-split.php'))
			$this->Page_Terminate("meteo-stazione-split.php");
		if ($Security->AllowList(CurrentProjectID() . 'stazioni_lookup'))
			$this->Page_Terminate("stazioni_lookup_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'stazioni_arsia_lookup'))
			$this->Page_Terminate("stazioni_arsia_lookup_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'monitoraggio-mosca-monoazienda.php'))
			$this->Page_Terminate("monitoraggio-mosca-monoazienda.php");
			$this->Page_Terminate("informativa_privacy.php");
		if ($Security->AllowList(CurrentProjectID() . 'aziende_soci_report'))
			$this->Page_Terminate("aziende_soci_report_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'monitoraggio-mosca-upload-csv.php'))
			$this->Page_Terminate("monitoraggio-mosca-upload-csv.php");
		if ($Security->AllowList(CurrentProjectID() . 'monitoraggio-mosca-upload-csv-import.php'))
			$this->Page_Terminate("monitoraggio-mosca-upload-csv-import.php");
		if ($Security->AllowList(CurrentProjectID() . 'dati_mosca_archivio'))
			$this->Page_Terminate("dati_mosca_archivio_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'modelli_mosca_archivio'))
			$this->Page_Terminate("modelli_mosca_archivio_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettini-gestione non usata.php'))
			$this->Page_Terminate("bollettini-gestione non usata.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettini-compilazione-step1.php'))
			$this->Page_Terminate("bollettini-compilazione-step1.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettini-archivio.php'))
			$this->Page_Terminate("bollettini-archivio.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettini'))
			$this->Page_Terminate("bollettini_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'zone'))
			$this->Page_Terminate("zone_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'avversita'))
			$this->Page_Terminate("avversita_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettino_html2pdf.php'))
			$this->Page_Terminate("bollettino_html2pdf.php");
		if ($Security->AllowList(CurrentProjectID() . 'blank non usata.php'))
			$this->Page_Terminate("blank non usata.php");
		if ($Security->AllowList(CurrentProjectID() . 'codici_prescrizione'))
			$this->Page_Terminate("codici_prescrizione_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'categ_utente'))
			$this->Page_Terminate("categ_utente_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'utenti_semia_esterni_report'))
			$this->Page_Terminate("utenti_semia_esterni_report_list.php");
		if ($Security->AllowList(CurrentProjectID() . 'bollettini-compilazione-step2.php'))
			$this->Page_Terminate("bollettini-compilazione-step2.php");
		if ($Security->AllowList(CurrentProjectID() . 'meteo-stat-legenda.php'))
			$this->Page_Terminate("meteo-stat-legenda.php");
		if ($Security->IsLoggedIn()) {
			$this->setFailureMessage($Language->Phrase("NoPermission") . "<br><br><a href=\"logout.php\">" . $Language->Phrase("BackToLogin") . "</a>");
		} else {
			$this->Page_Terminate("login.php"); // Exit and go to login page
		}
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

		/*

		// solo se pagina default è meteo
		if  (CurrentUserLevel()==4) {
			$url = "meteo-stazione-split.php";
		}
		*/
	}

	// Message Showing event
	// $type = ''|'success'|'failure'
	function Message_Showing(&$msg, $type) {

		// Example:
		//if ($type == 'success') $msg = "your success message";

	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($default)) $default = new cdefault();

// Page init
$default->Page_Init();

// Page main
$default->Page_Main();
?>
<?php include_once $EW_RELATIVE_PATH . "header.php" ?>
<?php
$default->ShowMessage();
?>
<?php include_once $EW_RELATIVE_PATH . "footer.php" ?>
<?php
$default->Page_Terminate();
?>

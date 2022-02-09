<?php

namespace StripeDecorated;

/**
 * Class StripeDecorated.
 * https://stripe.com/docs/payments/accept-a-payment-charges //START STRIPE HERE!
 * @copyright Copyright (c) 2021 Georges Jean-denis: georgesjeandenis@hotmail.com
 * @license   MIT License 
 */
class StripeDecorated
{
    public $version				= null;
    public $root				= null;
    public $parameters			= null;

    public $lang				= null;
    public $stripe_key			= null;
    public $stripe_live			= null;
    public $stripe_whois		= null;
    public $stripe_amount		= null;
    public $stripe_currency		= null;
    public $stripe_description	= null;
    public $stripe_item_name	= null;
    public $form_text_1			= null;
    public $form_text_2			= null;
    public $debug				= null;

    public $stripe_label		= null;
    public $lang_label			= null;
    public $message_marked		= null;
    public $message_intro		= null;
    public $message_color		= null;
    public $payment_page		= null;
    public $reveal				= null;


    public function __construct($parameters = array())
    {
        //avant tout
        $this->version			= "1.025";
        $this->root				= $_SERVER["DOCUMENT_ROOT"];
        $this->parameters		= $parameters;

        //parameters
		$this->stripe_live		  = isset($parameters["stripe_live"]) ? $parameters["stripe_live"] : ( isset($_GET["stripe_live"]) ? $_GET["stripe_live"] : 0 );

        $this->lang               = isset($parameters["lang"]) ? $parameters["lang"] : ( isset($_GET["lang"]) && $_GET["lang"] != ""?$_GET["lang"]:"fr" );
        $this->stripe_amount      = isset($parameters["stripe_amount"]) ? $parameters["stripe_amount"] : ( isset($_GET["stripe_amount"]) && $_GET["stripe_amount"] != ""?$_GET["stripe_amount"]:"0.50" );
        $this->stripe_currency    = isset($parameters["stripe_currency"]) ? $parameters["stripe_currency"] : ( isset($_GET["stripe_currency"]) && $_GET["stripe_currency"] != ""?$_GET["stripe_currency"]:"CAD" );
        $this->stripe_description = isset($parameters["stripe_description"]) ? $parameters["stripe_description"] : ( isset($_GET["stripe_description"]) && $_GET["stripe_description"] != ""?$_GET["stripe_description"]:"jewel description" );
        $this->stripe_item_name   = isset($parameters["stripe_item_name"]) ? $parameters["stripe_item_name"] : ( isset($_GET["stripe_item_name"]) && $_GET["stripe_item_name"] != ""?$_GET["stripe_item_name"]:"jewel" );
        $this->form_text_1        = isset($parameters["form_text_1"]) ? $parameters["form_text_1"] : ( isset($_GET["form_text_1"]) && $_GET["form_text_1"] != ""?$_GET["form_text_1"]:($this->lang=="fr"?"Carte de crédit ou de débit":"Credit or debit card") );
        $this->form_text_2        = isset($parameters["form_text_2"]) ? $parameters["form_text_2"] : ( isset($_GET["form_text_2"]) && $_GET["form_text_2"] != ""?$_GET["form_text_2"]:($this->lang=="fr"?"Soumettre le paiement":"Submit Payment") );
        $this->debug			  = isset($parameters["debug"]) ?$parameters["debug"] : (isset($_GET["debug"]) && ($_GET["debug"] == 1 || $_GET["debug"] == "on") ? 1 : 0);

        //commence le code
        $this->lang_label         = $this->lang== "fr" ? "fr":"en";
        $this->stripe_label		  = $this->stripe_live == 1 || $this->stripe_live == 2 ? "LIVE":"TEST";
        $this->stripe_whois       = $this->stripe_live == 1 ? "AMC" : ( $this->stripe_live == 2 ? "GEORGES" : "TEST" );
        $this->stripe_key         = $this->stripe_live == 1 ? "pk_live_XXX" : ( $this->stripe_live == 2 ? "pk_live_XXX" : "pk_test_XXX" );
        $this->message_marked     = $this->stripe_live == 1 ? "ATTENTION! L'ARGENT ENTRERA DANS LE COMPTE EN BANQUE ASSOCIÉ AU COMPTE STRIPE D'« ANNE MARIE CHAGNON INC. »" : ( $this->stripe_live == 2 ? "ATTENTION! L'ARGENT ENTRERA DANS LE COMPTE EN BANQUE DE GEORGES" : "rien ne sera transféré" );
        $this->message_intro      = $this->stripe_live == 1 || $this->stripe_live == 2 ? "LA PAGE EST LIVE : " : "LA PAGE EST EN MODE TEST : ";
        $this->message_color      = $this->stripe_live == 1 ? "#fa6" : ( $this->stripe_live == 2 ? "#f00" : "#0f0" ) ;
        $this->payment_page       = $_SERVER["PHP_SELF"];
        $this->reveal			  = "\$this->debug:" . $this->debug . ($parameters ? ":<br />construct \$parameters:" . print_r($parameters, 1) .":":":parameters are EMPTY:");

		$this->getStripeFrontEnd();
		$this->getStripeBackEnd();
    }

    /**
     * @enables echoing the instantiation
     */
	public function __toString()
	{
		return $this->getStripeFrontEnd() . "<!--//-->";
	}

    /**
     * @return the HTML frontend of Stripe
     */
    public function getStripeFrontEnd()
    {
		$test_string =

		$this->debug == 1 ?"
		<title>Stripe Decorated</title>
		<meta charset=\"utf-8\" />
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=10.0, user-scalable=1\" />
		<link	type=\"image/x-icon\" href=\"data:image/png;base64, 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\" rel=\"shortcut icon\" />
		<style>
			body
			{
			    position: relative;
			    top: 50px;
			}
		</style>
		": "";

		if( !isset($_POST["stripe_amount"]) ) {
			return $test_string .
			"
			<header class=\"" . ($this->debug?"":"debug") . "\">
				<h3>
					<b>" . $this->message_intro . "</b>
					<mark>" . $this->message_marked . "</mark>
				</h3>
				<i>*" . $this->version . "*</i><br />
		        <b>\"reveal:\"" . $this->reveal . "</b><br />
		        <i>\"stripe_live:\"" . $this->stripe_live . "</i><br />
			</header>
			<div id=\"stripe\">
				<div action=\"" . $this->payment_page . "\" method=\"post\" id=\"payment-form\">
					<div class=\"form-row\">
						<label for=\"card-element\">" . $this->form_text_1 . "</label>
						<div id=\"card-element\"></div>
						<div id=\"card-errors\" role=\"alert\"></div>
					</div>
					<section>
						<input id=\"stripe_key\"         	name=\"stripe_key\"          	type=\"" . ($this->debug?"text":"hidden") . "\"   	value=\"" . $this->stripe_key . "\"                 class=\"stripe_key\"		readonly />
						<input id=\"stripe_lang\"         	name=\"stripe_lang\"          	type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"" . $this->lang_label . "\"                 class=\"stripe_lang\"		readonly />
						<input id=\"stripe_live\"         	name=\"stripe_live\"          	type=\"" . ($this->debug?"text":"hidden") . "\"   	value=\"" . $this->stripe_live . "\"                class=\"stripe_live\"		readonly />
						<input id=\"stripe_whois\"         	name=\"stripe_whois\"          	type=\"" . ($this->debug?"text":"hidden") . "\"   	value=\"" . $this->stripe_whois . "\"               class=\"stripe_whois\"		readonly />
						<input id=\"stripe_label\"			name=\"stripe_label\"			type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"" . $this->stripe_label . "\"				class=\"stripe_label\"		readonly />
						<input id=\"stripe_amount\"			name=\"stripe_amount\"			type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"" . $this->stripe_amount . "\"              class=\"stripe_amount\" />
						<input id=\"stripe_currency\"		name=\"stripe_currency\"		type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"" . $this->stripe_currency . "\"            class=\"stripe_currency\" />
						<input id=\"stripe_description\"	name=\"stripe_description\"		type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"TEST: " . $this->stripe_description . "\"	class=\"stripe_description\" />
						<input id=\"stripe_item_name\"		name=\"stripe_item_name\"     	type=\"" . ($this->debug?"text":"hidden") . "\"	    value=\"TEST: " . $this->stripe_item_name . "\"		class=\"stripe_item_name\" />
					</section>
					<button id=\"stripe_submiter\"  onclick=\"stripe_submit()\" class=\"button checkout-cta-back\">" . $this->form_text_2 . "</button>
				</div>
				<script src=\"https://js.stripe.com/v3/\"></script>
				<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js\"></script>
				<script>
				    var checkout_form = document.forms[0];
				    var validation_result = false;
    				var card        = null;
    				var stripe      = null;

    				function stripe_submit()
    				{
    				    $(checkout_form).validate({submitHandler:onFormSubmit()});
    				    validation_result = $(checkout_form).valid();
    				    " . ($this->debug?"alert('valid: ' + validation_result);":"console.log('valid: ' + validation_result);") . "
    				    real_stripe_submit();
    				}
					function real_stripe_submit()
					{
						if(validation_result)
						{
							stripe.createToken(card).then
							(
								function(result)
								{
									if (result.error)
									{
										var errorElement = document.getElementById(\"card-errors\");
										errorElement.textContent = result.error.message;
									}
									else
									{
										$(\"#stripe_amount\").val($(\"#stripe_amount\").val()*100);
										console.log(\"stripe_amount: \" + $(\"#stripe_amount\").val());
										document.getElementById(\"stripe_submiter\").style.cssText = \"display: none;\";
										stripeTokenHandler(result.token);
									}
								}
							);
						}
					}
					function stripeTokenHandler(token)
					{
						var hiddenInput = document.createElement(\"input\");
						hiddenInput.setAttribute(\"type\", \"hidden\");
						hiddenInput.setAttribute(\"name\", \"stripeToken\");
						hiddenInput.setAttribute(\"value\", token.id);
						checkout_form.appendChild(hiddenInput);
						checkout_form.submit();
					}
    				function onFormSubmit()
    				{
    				    console.log(\"submitting...\");
    				}
    				$(document).ready
    				(	function()
    					{
    						console.log(\"stripe_amount: \" + document.getElementById(\"stripe_amount\").value);
    						stripe      = Stripe(\"" . $this->stripe_key . "\");
    						var elements    = stripe.elements({locale:\"" . ( !isset($this->lang) || $this->lang=="fr" ?"fr":"en" ) . "\"});
    						card        = elements.create
    						(
    							\"card\",
    							{
    								hidePostalCode: true,
    								iconStyle: \"solid\",
    								style:
    								{
    									base:
    									{
    										iconColor: \"#8898AA\",
    										color: \"#000\",
    										fontWeight: 300,
    										fontSize: \"19px\",
    									
    										\"::placeholder\":
    										{
    											color: \"#8898AA\",
    										},
    									},
    									invalid:
    									{
    										iconColor: \"#e85746\",
    										color: \"#e85746\",
    									}
    								},
    								classes:
    								{
    									focus: \"input\",
    									empty: \"is-empty\",
    								},
    							}
    						);
    						card.mount(\"#card-element\");
    					}
    				);
				</script>
				<style>
					html, body
					{
					    border: 0;
					    margin: 0;
					    padding: 0;
					    width: 100%;
					    height: 100%;
					}
					section
					{
					    position: relative;
					    top: 0px;
					    display: block;
					    width: 100%;
					    margin: 20px 0px;
					    padding:  0px;
					    border: 0px solid;
					}
					header 
					{
					    position: relative;
					    top: 0px;
					    display: block;
					    width: auto;
					    margin:  0px 0px;
					    padding: 10px;
					    border: 1px solid; 
					    background: " . $this->message_color . ";
					}
					#card-element
					{
						background: rgba(255, 255, 205, 0.9);
						border-radius: 4px;
						border: 2px solid #000;
						display: table-cell;
						margin: 15px 0px 0px 0px;
						padding: 0px 15px 0px 25px;
						width: 820px;
					}
					#stripe label
					{
						text-transform: uppercase;
					}
					#stripe form
					{
						width: 500px;
						padding: 10px;
						border: 1px solid;
					}
					#stripe input[type=text] 
					{
						width: 100%;
						padding: 2px 5px;
						background: rgba(255, 255, 205, 0.9);
					}
					#stripe input:read-only
					{
						background: #eee !important;
						border: 2px solid !important;
						border-style: dotted !important;
					}
					#stripe input[type=submit], input[type=radio], .label, .notes, .detail, .ref
					{
						cursor: pointer;
					}
					#stripe mark
					{
						border-radius: 5px;
						padding: 5px 20px 8px 20px;
						" . ( $this->stripe_live == 1 ? "margin: 10px 0px 0px 0px;": ( $this->stripe_live == 2 ? "margin: 10px 0px 0px 0px;" : "margin: 0px 0px 0px 5px;" ) ) . "
						" . ( $this->stripe_live == 1 || $this->stripe_live == 2 ? "display: block;":"" ) . "
					}
					.stripe_label
					{
						text-transform: uppercase;
					}
					.stripe_lang
					{
						text-transform: uppercase;
					}
					.stripe_amount
					{
						text-transform: uppercase;
					}
					.stripe_currency
					{
						text-transform: uppercase;
					}
					.stripe_description
					{
						text-transform: uppercase;
					}
					.stripe_item_name
					{
						text-transform: uppercase;
					}
					.debug
					{
						display: none !important;
						margin: 08px 08px 08px 08px;
						padding: 02px 02px 02px 02px;
					}
					.tippy-tooltip.light-theme
					{
						position: relative;
						top: 20px;
						left: 100px;
						background: #ff0;
						border: 1px solid #000;
						font-weight: bold;
						color: #333;
					}
				</style>
			</div>";
		}
    }

    /**
     * @return the PHP backend of Stripe
     */
    public function getStripeBackEnd()
    {
		echo "<style>.debug{display: none !important;}</style>\n\n";
		echo "<div class=\"debug\">getStripeBackEnd() has been called...\nHowever, it only executes other code apart from this line WHEN \$_POST isset && !empty(\$_POST)...\n\n<br /><br />";

		//avant tout 
			global $status, $charge, $error, $error_detailed;
			$status = $charge = $error =  $error_detailed = 0;

			$root = $_SERVER["DOCUMENT_ROOT"];
			require_once($root . "/vendor/autoload.php");
			require_once($root . "/vendor/stripe/stripe-php/init.php"); //NO NEED, IF "composer require stripe/stripe-php" worked UNLESS INSTALLED INSIDE VENDOR MANUALLY!
		/*
			//JUST TO MAKE CLEAR :

			sk_live_XXX //AMC
			sk_live_XXX //GEORGES
			sk_test_XXX //SANDBOX
		*/

		if( isset($_POST) && isset($_POST["stripe_amount"]) )
		{
			try
			{
				$stripe_live	    = $_POST["stripe_live"];
				$stripe_key		    = $stripe_live == 1 ?"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": ( $stripe_live == 2 ? "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" : "sk_test_51JzgKMGng1qmQfDnt5o9MbOJBBJ2j9SVz3nmSfWPV01YWHrC9kAyTtdPnrcfhNhm1TzYyJZgsns2rGEV6zMxoikd0089W1fJo3" );

				\Stripe\Stripe::setApiKey($stripe_key);

				$amount			= round($_POST["stripe_amount"]);
				$currency		= $_POST["stripe_currency"];
				$description	= $_POST["stripe_description"];

				$token = $_POST["stripeToken"];
				$charge = \Stripe\Charge::create
				([
					"amount" => $amount,
					"currency" => $currency,
					"description" => $description,
					"source" => $token,
				]);
				$status = $charge->getLastResponse()->json["status"];
			}
			catch(Stripe_CardError $e)
			{
				$error = $e->getMessage();
			}
			catch (Stripe_InvalidRequestError $e)
			{
				$error = $e->getMessage();
			}
			catch (Stripe_AuthenticationError $e)
			{
				$error = $e->getMessage();
			}
			catch (Stripe_ApiConnectionError $e)
			{
				$error = $e->getMessage();
			}
			catch (Stripe_Error $e)
			{
				$error = $e->getMessage();
			}
			catch (Exception $e)
			{
				$error = $e->getMessage();
			}
			
			$error_detailed = (stristr($error, "This transaction requires authentication")) ?"The customer should try again and authenticate their card when prompted during the transaction.Payment declined by customer's bank":"";
			//https://lespointscom.com/a/html/stripe_decline_codes.html
			//https://dashboard.stripe.com/payments/ ( check this link on proper account to find out what is going on )

			echo "error: $error" . ": $error_detailed" . "\n<br />";
			echo "status: $status" . "\n<br />";

			if($status == "succeeded")
			{
				echo "succeeded" . "\n<br />";
				//email someone or stupid log it
				//header("location:/stripe/merci/"); //uncomment this line!
				echo "headers[Request-Id]: " . ($charge->getLastResponse()->headers["Request-Id"]) . "\n<br />";
			}
			else
			{
				echo "failed" . "\n<br />";
				//header("location:/stripe/error/?error=" . $error); //uncomment this line!
			}
		}
		else
		{
			//header("location:/stripe/error/"); //uncomment this line!
		}

		$echo = "\n\n<br /><br />\$_POST 1: \n" . print_r($_POST, 1);
		echo $echo . "</div>\n\n";
	}
}

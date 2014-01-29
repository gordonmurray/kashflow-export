<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = TRUE;

$app['kashflow_username'] = '';
$app['kashflow_password'] = '';

$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__ . '/views',));

$app->get(
	'/',
	function () use ($app) {
		return $app['twig']->render('index.html.twig');
	}
);

/*
 * ------------------------------------------
 * View
 * ------------------------------------------
 */
$app->get(
	'/customers',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$customers = $kashflow->GetCustomers();

		return $app['twig']->render('customers.html.twig', array('customers' => $customers));
	}
);

$app->get(
	'/invoices',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(array('StartDate' => '2001-01-01', 'EndDate' => '2020-01-01'));

		return $app['twig']->render('invoices.html.twig', array('invoices' => $invoices));
	}
);

$app->get(
	'/invoice_payments',
	function () use ($app) {

		$invoice_payments = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(array('StartDate' => '2001-01-01', 'EndDate' => '2020-01-01'));

		foreach ($invoices['Invoice'] as $invoice) {

			$payment = $kashflow->GetInvoicePayment(array('InvoiceNumber' => $invoice['InvoiceNumber']));

			if (is_array($payment) && !empty($payment)) {
				$invoice_payments[] = $payment;
			}
		}

		return $app['twig']->render('invoice_payments.html.twig', array('invoice_payments' => $invoice_payments));
	}

);

$app->get(
	'/suppliers',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$suppliers = $kashflow->getSuppliers();

		return $app['twig']->render('suppliers.html.twig', array('suppliers' => $suppliers));
	}
);

$app->get(
	'/quotes',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$quotes = $kashflow->getQuotes();

		return $app['twig']->render('quotes.html.twig', array('quotes' => $quotes));
	}
);

/*
 * ------------------------------------------
 * Export
 * ------------------------------------------
 */

$app->get(
	'/customers_export',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$customers = $kashflow->GetCustomers();

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=customers.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($customers as $row) {
			fputcsv($output, $row, ',');
		}

		fclose($output);
		exit();
	}
);


$app->get(
	'/invoices_export',
	function () use ($app) {

		$updated_invoices_array = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(
			array('StartDate' => '2007-01-01', 'EndDate' => '2015-01-01')
		);

		/**
		 * Remove a 'Lines' sub array
		 */
		$invoices = $invoices['Invoice'];
		foreach ($invoices as $invoice_details) {
			unset($invoice_details['Lines']);
			$updated_invoices_array[] = $invoice_details;
		}

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=invoices.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($updated_invoices_array as $row) {
			fputcsv($output, $row, ',');
		}

		fclose($output);
		exit();

	}
);

$app->get(
	'/invoice_payments_export',
	function () use ($app) {

		$invoice_payments = array();
		$updated_invoice_payments = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(array('StartDate' => '2001-01-01', 'EndDate' => '2020-01-01'));

		foreach ($invoices['Invoice'] as $invoice) {

			$payment = $kashflow->GetInvoicePayment(array('InvoiceNumber' => $invoice['InvoiceNumber']));

			if (is_array($payment) && !empty($payment)) {
				$invoice_payments[] = $payment[0];
			}
		}

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=invoice_payments.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($invoice_payments as $row) {
			fputcsv($output, $row, ',');
		}

		fclose($output);
		exit();

	}
);


$app->get(
	'/suppliers_export',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$suppliers = $kashflow->getSuppliers();

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=suppliers.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($suppliers as $row) {
			fputcsv($output, $row, ',');
		}

		fclose($output);
		exit();

	}
);

$app->get(
	'/quotes_export',
	function () use ($app) {

		$updated_array = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$quotes = $kashflow->getQuotes();

		/**
		 * Remove a 'Lines' sub array
		 */
		foreach ($quotes as $quote_details) {
			unset($quote_details['Lines']);
			$updated_array[] = $quote_details;
		}

		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=quotes.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($updated_array as $row) {
			fputcsv($output, $row, ',');
		}

		fclose($output);
		exit();
	}
);

$app->get(
	'/quotes_export_pdfs',
	function () use ($app) {

		$updated_array = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$quotes = $kashflow->getQuotes();

		foreach ($quotes as $quote) {
			$quote_pdf = file_get_contents($quote['PermaLink']);

			file_put_contents('export_data/quote_' . $quote['InvoiceNumber'] . '.pdf', $quote_pdf);
		}

		return 'Exported to /export_data/';
	}
);

$app->get(
	'/invoices_export_pdfs',
	function () use ($app) {

		$updated_array = array();

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(
			array('StartDate' => '2007-01-01', 'EndDate' => '2015-01-01')
		);

		foreach ($invoices['Invoice'] as $invoice) {

			$pdf = file_get_contents($invoice['PermaLink']);

			file_put_contents('export_data/invoice_' . $invoice['InvoiceNumber'] . '.pdf', $pdf);
		}

		return 'Exported to /export_data/';
	}
);

/*
 * ------------------------------------------
 * Delete
 * ------------------------------------------
 */

$app->get(
	'/customers_delete',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$customers = $kashflow->GetCustomers();

		foreach ($customers as $customer) {
			if (is_array($customer) && !empty($customer)) {

				$response = $kashflow->DeleteCustomer($customer);

				if (isset($response['ErrMsg'])) {
					echo "Error deleting Customer " . $customer['CustomerID'] . "<br />\n";
				} else {
					echo "Deleted Customer " . $customer['CustomerID'] . "<br />\n";
				}
			}
		}

		return TRUE;


	}
);

$app->get(
	'/invoices_delete',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(array('StartDate' => '2001-01-01', 'EndDate' => '2020-01-01'));

		foreach ($invoices['Invoice'] as $invoice) {
			$response = $kashflow->DeleteInvoice($invoice);

			if (isset($response['ErrMsg'])) {
				echo "Error deleting invoice " . $invoice['InvoiceNumber'] . "<br />\n";
			} else {
				echo "Deleted invoice " . $invoice['InvoiceNumber'] . "<br />\n";
			}
		}

		return TRUE;
	}
);

$app->get(
	'/invoice_payments_delete',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$invoices = $kashflow->getInvoicesByDateRange(array('StartDate' => '2001-01-01', 'EndDate' => '2020-01-01'));

		if (isset($invoices['Invoice']) && !empty($invoices['Invoice'])) {
			foreach ($invoices['Invoice'] as $invoice) {

				$payment = $kashflow->GetInvoicePayment(array('InvoiceNumber' => $invoice['InvoiceNumber']));

				if (is_array($payment) && !empty($payment)) {
					$invoice_payments[] = $payment[0];
				}
			}

			foreach ($invoice_payments as $payment) {
				$response = $kashflow->DeleteInvoicePayment($payment);

				if (isset($response['ErrMsg'])) {
					echo "Error deleting payment " . $payment['PayID'] . "<br />\n";
				} else {
					echo "Deleted payment " . $payment['PayID'] . "<br />\n";
				}
			}
		}

		return TRUE;
	}
);

$app->get(
	'/quotes_delete',
	function () use ($app) {

		$kashflow = new Kashflow($app['kashflow_username'], $app['kashflow_password']);

		$quotes = $kashflow->getQuotes();

		foreach ($quotes as $quote) {
			if (is_array($quote) && !empty($quote)) {

				$response = $kashflow->DeleteQuote($quote);

				if (isset($response['ErrMsg'])) {
					echo "Error deleting Quote " . $quote['InvoiceNumber'] . "<br />\n";
				} else {
					echo "Deleted Quote " . $quote['InvoiceNumber'] . "<br />\n";
				}
			}
		}

		return TRUE;
	}
);

$app->run();
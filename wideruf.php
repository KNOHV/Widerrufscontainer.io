<?php
// Setzt den Header für die Rückgabe an das JavaScript
header('Content-Type: application/json');

// Prüfen, ob die Daten per POST gesendet wurden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Daten bereinigen und auslesen
    $name = htmlspecialchars(strip_tags($_POST['name'] ?? ''));
    $contractId = htmlspecialchars(strip_tags($_POST['contractId'] ?? ''));
    $customerEmail = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    // Pflichtfelder prüfen
    if (empty($name) || empty($contractId) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Bitte füllen Sie alle Felder korrekt aus."]);
        exit;
    }

    $datum = date('d.m.Y H:i:s');

    // --------------------------------------------------------
    // 1. E-Mail an SIE (Kummers Onlinehandel)
    // --------------------------------------------------------
    $toShop = "Reklamation@kummers-onlinehandel.de";
    $subjectShop = "Neuer Widerruf eingegangen: " . $contractId;
    
    $messageShop = "Ein neuer Online-Widerruf wurde übermittelt:\n\n";
    $messageShop .= "Kunde: $name\n";
    $messageShop .= "Bestell-/Vertragsnummer: $contractId\n";
    $messageShop .= "E-Mail des Kunden: $customerEmail\n";
    $messageShop .= "Zeitpunkt: $datum\n";
    
    // Header für die Shop-E-Mail
    $headersShop = "From: noreply@kummers-onlinehandel.de\r\n";
    $headersShop .= "Reply-To: $customerEmail\r\n";
    $headersShop .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // --------------------------------------------------------
    // 2. Bestätigungs-E-Mail an den KUNDEN (Gesetzliche Pflicht)
    // --------------------------------------------------------
    $subjectCustomer = "Eingangsbestätigung Ihres Widerrufs";
    
    $messageCustomer = "Guten Tag $name,\n\n";
    $messageCustomer .= "wir bestätigen hiermit den Eingang Ihres Widerrufs für die Bestellnummer $contractId.\n\n";
    $messageCustomer .= "Ihr Widerruf wurde am $datum elektronisch in unserem System erfasst und wird nun bearbeitet.\n\n";
    $messageCustomer .= "Mit freundlichen Grüßen\nIhr Team von Kummers Onlinehandel";
    
    // Header für die Kunden-E-Mail
    $headersCustomer = "From: Reklamation@kummers-onlinehandel.de\r\n";
    $headersCustomer .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // --------------------------------------------------------
    // E-Mails versenden
    // --------------------------------------------------------
    $mailShopSent = mail($toShop, $subjectShop, $messageShop, $headersShop);
    $mailCustomerSent = mail($customerEmail, $subjectCustomer, $messageCustomer, $headersCustomer);

    // Prüfen, ob beide E-Mails erfolgreich an den Server übergeben wurden
    if ($mailShopSent && $mailCustomerSent) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Fehler beim Mailversand. Bitte prüfen Sie Ihre Server-Konfiguration."]);
    }

} else {
    // Falls die Datei direkt im Browser aufgerufen wird
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Unerlaubte Aufrufmethode."]);
}
?>
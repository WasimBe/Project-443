<?php

$emailadres = readline("Voer je e-mailadres in: ") . PHP_EOL;

if ($emailadres === "gebruiker@443.nl") {
    echo "Correct, volgende:" . PHP_EOL;
} else { echo "Incorrect, probeer opnieuw" . PHP_EOL; }
 
$wachtwoord = readline("Voer je wachtwoord in:") . PHP_EOL;

if ($wachtwoord === "443344") {
    echo "Correct, je bent ingelogd!" . PHP_EOL;
} else { echo "Jammer incorrect, was de laatste, opnieuw!" . PHP_EOL; }

?>
# Live eingespielt am 2026-10-09

Kopien der zwei neuen mu-plugins (Stand beim Deploy). Live-Version immer per
execute-php neu holen, bevor sie geaendert wird.

- `sp-wallet-safeguards.php` - Kundenkonto Pflicht bei Abo/Aufladung, kein
  Rabatt auf Aufladungen, Gutschrift max. bezahlter Betrag, Gutschein-Schutz.
- `sp-abo-stack-billing.php` - Stack-weise Abrechnung, sofortiges Fortsetzen
  nach Aufladung, Erinnerung 7+2 Tage, Stack-Mails.

Produkt 729 (Guthaben aufladen): sold_individually=yes, slicewp_disable_commissions=1.

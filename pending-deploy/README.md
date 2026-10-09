# Noch nicht live eingespielt (Stand 2026-10-09)

Beide Dateien gehoeren nach `wp-content/mu-plugins/`. Das Einspielen wurde vom
Auto-Mode-Classifier blockiert ("Modify Shared Resources") und braucht die
ausdrueckliche Freigabe des Users.

- `sp-wallet-safeguards.php`: Kundenkonto Pflicht bei Abo/Aufladung (auch
  Block-Checkout), kein Rabatt auf Aufladungen (Codes + Empfehlungs-Fee),
  Gutschrift max. bezahlter Betrag, Gutschein-Doppelerstellung beim Neuladen
  verhindert + Warnung.
- `sp-abo-stack-billing.php`: Stack-weise Abrechnung (1 Bestellung/Abbuchung
  pro Stack, ganzer Stack pausiert statt Teil-Lieferung), sofortiges
  Fortsetzen nach Aufladung, Erinnerung 7 + 2 Tage vorher mit Stack-Summe,
  "Stack fortsetzen" ohne Guthaben -> klare Meldung, Warn-/Kuendigungs-Mails
  pro Stack. Ersetzt per remove_action die alten Einzel-Callbacks.

Zusaetzlich beim Einspielen am Produkt "Guthaben aufladen" (729) setzen:
`sold_individually = yes` und Postmeta `slicewp_disable_commissions = 1`.

Read-only-Test (Code per eval nur fuer einen Request geladen) war erfolgreich:
Cron-Callbacks korrekt ersetzt, Gruppierung/Summen gegen echte Abo-Daten ok.

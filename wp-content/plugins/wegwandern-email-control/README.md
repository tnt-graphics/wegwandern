# Wegwandern Email Control

Ein WordPress-Plugin zur Kontrolle von WordPress Core E-Mail-Benachrichtigungen für Benutzerregistrierung, Kontoänderungen und Kommentare.

## Beschreibung

Dieses Plugin ermöglicht es Ihnen, die Standard-E-Mail-Benachrichtigungen von WordPress zu deaktivieren, die normalerweise bei folgenden Ereignissen gesendet werden:

- **Benutzerregistrierung**: E-Mails an Admin und neue Benutzer
- **Kontoänderungen**: E-Mails bei Änderung von E-Mail-Adresse oder Passwort
- **Kommentare**: E-Mails an Moderator und Post-Autor bei neuen Kommentaren
- **Passwort-Reset**: Admin-Benachrichtigungen bei Passwort-Reset

## Features

✅ **Selektive Deaktivierung**: Wählen Sie genau, welche E-Mail-Typen deaktiviert werden sollen
✅ **Gipfelbuch-Filter**: Option, E-Mails nur für Gipfelbuch-Benutzer (summit-book-user) zu deaktivieren
✅ **Benutzerfreundliche Oberfläche**: Einfache Einstellungsseite im WordPress-Backend
✅ **Übersichts-Dashboard**: Zeigt den aktuellen Status aller E-Mail-Einstellungen
✅ **Kompatibel**: Funktioniert mit allen WordPress Core E-Mails

## Installation

1. Laden Sie den `wegwandern-email-control` Ordner in das `/wp-content/plugins/` Verzeichnis hoch
2. Aktivieren Sie das Plugin über das 'Plugins' Menü in WordPress
3. Gehen Sie zu Einstellungen → Email Control, um das Plugin zu konfigurieren

## Verwendung

### Einstellungen konfigurieren

1. Navigieren Sie zu **Einstellungen → Email Control**
2. Wählen Sie die E-Mail-Typen aus, die Sie deaktivieren möchten:
   - ☑️ Registrierungs-E-Mails deaktivieren
   - ☑️ Kontoänderungs-E-Mails deaktivieren
   - ☑️ Kommentar-Benachrichtigungen deaktivieren
   - ☑️ Passwort-Reset-Benachrichtigungen deaktivieren
3. Optional: Aktivieren Sie "Nur für Gipfelbuch-Benutzer", um die Einstellungen nur auf Benutzer mit der Rolle `summit-book-user` anzuwenden
4. Klicken Sie auf "Einstellungen speichern"

### Filter-Modi

**Alle Benutzer**: Wenn "Nur für Gipfelbuch-Benutzer" **deaktiviert** ist, werden die E-Mail-Einstellungen auf alle Benutzer angewendet.

**Nur Gipfelbuch-Benutzer**: Wenn "Nur für Gipfelbuch-Benutzer" **aktiviert** ist:
- Registrierungs- und Kontoänderungs-E-Mails werden nur für Benutzer mit der Rolle `summit-book-user` deaktiviert
- Kommentar-Benachrichtigungen werden nur für Kommentare auf Gipfelbuch-Post-Typen deaktiviert (`community_beitrag`, `pinnwand_eintrag`, `bewertung`)
- Andere Benutzer und Post-Typen erhalten weiterhin E-Mails

## Technische Details

### Hooks und Filter

Das Plugin verwendet folgende WordPress-Filter:

- `wp_new_user_notification_email_admin` - Deaktiviert Admin-Benachrichtigung bei Neuregistrierung
- `wp_new_user_notification_email` - Deaktiviert Benutzer-Willkommens-E-Mail
- `send_email_change_email` - Deaktiviert E-Mail bei E-Mail-Adress-Änderung
- `send_password_change_email` - Deaktiviert E-Mail bei Passwort-Änderung
- `send_password_reset_email` - Deaktiviert Admin-Benachrichtigung bei Passwort-Reset
- `notify_moderator` - Deaktiviert Moderator-Benachrichtigung bei neuen Kommentaren
- `notify_post_author` - Deaktiviert Post-Autor-Benachrichtigung bei neuen Kommentaren

### Datenbankoptionen

Das Plugin speichert seine Einstellungen in der WordPress-Options-Tabelle:

```php
Option Name: wegw_email_control_settings
Structure:
array(
    'disable_registration_emails'    => bool,
    'disable_profile_change_emails'  => bool,
    'disable_comment_notifications'  => bool,
    'only_gipfelbuch_users'          => bool,
    'disable_password_reset_emails'  => bool,
)
```

## Kompatibilität

- **WordPress Version**: 5.2 oder höher
- **PHP Version**: 7.2 oder höher
- **Kompatibel mit**:
  - Wegwandern Summit Book Plugin
  - Wegw B2B Plugin
  - Formidable Forms

## Häufig gestellte Fragen

**F: Werden die E-Mails sofort deaktiviert?**
A: Ja, sobald Sie das Plugin aktivieren und die Einstellungen konfigurieren, werden die entsprechenden E-Mails nicht mehr gesendet.

**F: Kann ich die E-Mails später wieder aktivieren?**
A: Ja, deaktivieren Sie einfach die entsprechenden Checkboxen in den Einstellungen oder deaktivieren Sie das gesamte Plugin.

**F: Werden auch die Passwort-Reset-E-Mails an Benutzer deaktiviert?**
A: Nein, standardmäßig werden nur die Admin-Benachrichtigungen bei Passwort-Resets deaktiviert. Die eigentlichen Passwort-Reset-E-Mails an Benutzer bleiben aktiv, damit Benutzer ihr Passwort zurücksetzen können.

**F: Was passiert mit bestehenden Benutzern?**
A: Das Plugin beeinflusst nur zukünftige E-Mail-Benachrichtigungen. Bestehende Benutzer sind nicht betroffen.

## Changelog

### 1.0.0 (2025-10-27)
- Erste Version
- Deaktivierung von Registrierungs-E-Mails
- Deaktivierung von Kontoänderungs-E-Mails
- Deaktivierung von Kommentar-Benachrichtigungen
- Deaktivierung von Passwort-Reset-Benachrichtigungen
- Option für Gipfelbuch-spezifische Filterung
- Admin-Einstellungsseite mit Übersicht

## Support

Für Support und Anfragen kontaktieren Sie bitte:
- **Website**: https://www.pitsolutions.ch/
- **E-Mail**: info@pitsolutions.ch

## Lizenz

GPL v2 or later - http://www.gnu.org/licenses/gpl-2.0.txt

## Credits

Entwickelt von PITS (PIT Solutions)


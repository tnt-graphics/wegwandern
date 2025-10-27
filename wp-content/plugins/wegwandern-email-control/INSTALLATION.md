# Installation und Konfiguration

## Schritt-für-Schritt Anleitung

### 1. Plugin aktivieren

1. Gehen Sie im WordPress-Backend zu **Plugins → Installierte Plugins**
2. Suchen Sie nach "Wegwandern Email Control"
3. Klicken Sie auf **Aktivieren**

### 2. Plugin konfigurieren

1. Navigieren Sie zu **Einstellungen → Email Control**
2. Sie sehen die Einstellungsseite mit folgenden Optionen:

#### Verfügbare Einstellungen:

```
☑️ Registrierungs-E-Mails deaktivieren
   └─ Keine E-Mails bei Neuregistrierung senden
   
☑️ Kontoänderungs-E-Mails deaktivieren
   └─ Keine E-Mails bei Änderungen der Kontodaten senden
   
☑️ Kommentar-Benachrichtigungen deaktivieren
   └─ Keine E-Mails bei neuen Kommentaren senden
   
☑️ Passwort-Reset-Benachrichtigungen deaktivieren
   └─ Keine Admin-Benachrichtigung bei Passwort-Reset
   
☐ Nur für Gipfelbuch-Benutzer
   └─ E-Mails nur für Gipfelbuch-Benutzer deaktivieren
      (andere Benutzer erhalten weiterhin E-Mails)
```

### 3. Empfohlene Konfiguration

#### Option A: Alle E-Mails deaktivieren (für alle Benutzer)

```
✅ Registrierungs-E-Mails deaktivieren
✅ Kontoänderungs-E-Mails deaktivieren
✅ Kommentar-Benachrichtigungen deaktivieren
✅ Passwort-Reset-Benachrichtigungen deaktivieren
❌ Nur für Gipfelbuch-Benutzer (NICHT aktiviert)
```

**Ergebnis**: Keine E-Mail-Benachrichtigungen für ALLE Benutzer

---

#### Option B: Nur für Gipfelbuch-Benutzer deaktivieren

```
✅ Registrierungs-E-Mails deaktivieren
✅ Kontoänderungs-E-Mails deaktivieren
✅ Kommentar-Benachrichtigungen deaktivieren
✅ Passwort-Reset-Benachrichtigungen deaktivieren
✅ Nur für Gipfelbuch-Benutzer (AKTIVIERT)
```

**Ergebnis**: 
- Gipfelbuch-Benutzer (summit-book-user): Keine E-Mails
- Andere Benutzer (Admin, B2B-User, etc.): Erhalten weiterhin E-Mails

---

#### Option C: Nur bestimmte E-Mails deaktivieren

Beispiel: Nur Kommentar-Benachrichtigungen deaktivieren

```
❌ Registrierungs-E-Mails deaktivieren (NICHT aktiviert)
❌ Kontoänderungs-E-Mails deaktivieren (NICHT aktiviert)
✅ Kommentar-Benachrichtigungen deaktivieren
❌ Passwort-Reset-Benachrichtigungen deaktivieren (NICHT aktiviert)
❌ Nur für Gipfelbuch-Benutzer (NICHT aktiviert)
```

**Ergebnis**: Nur Kommentar-E-Mails werden für alle Benutzer deaktiviert

### 4. Einstellungen speichern

Klicken Sie auf **"Einstellungen speichern"** am Ende der Seite.

Sie sehen die Meldung: **"Einstellungen gespeichert"**

### 5. Status überprüfen

Nach dem Speichern sehen Sie zwei Übersichtstabellen:

#### Tabelle 1: Übersicht der E-Mail-Einstellungen

| E-Mail Typ | Status | Beschreibung |
|------------|--------|--------------|
| Benutzerregistrierung | ❌ Deaktiviert / ✅ Aktiv | E-Mails an Admin und Benutzer bei Neuregistrierung |
| Kontoänderungen | ❌ Deaktiviert / ✅ Aktiv | E-Mails bei Änderung von E-Mail-Adresse oder Passwort |
| Kommentare | ❌ Deaktiviert / ✅ Aktiv | E-Mails an Moderator und Post-Autor bei neuen Kommentaren |
| Passwort-Reset | ❌ Deaktiviert / ✅ Aktiv | Admin-Benachrichtigung bei Passwort-Reset |

#### Tabelle 2: Filter-Modus

**Aktueller Modus:**
- 🌐 **Alle Benutzer** - Einstellungen gelten für alle Benutzer
- ⚠️ **Nur Gipfelbuch-Benutzer** - Einstellungen gelten nur für Gipfelbuch-Benutzer

## Testen

### Test 1: Registrierung
1. Erstellen Sie einen neuen Test-Benutzer
2. Überprüfen Sie Ihren E-Mail-Posteingang
3. ✅ Wenn deaktiviert: Keine E-Mail erhalten
4. ❌ Wenn aktiv: E-Mail mit Willkommensnachricht erhalten

### Test 2: Kommentare
1. Erstellen Sie einen neuen Kommentar (als angemeldeter Benutzer oder Gast)
2. Überprüfen Sie den Admin-Posteingang
3. ✅ Wenn deaktiviert: Keine Benachrichtigung
4. ❌ Wenn aktiv: Benachrichtigung über neuen Kommentar

### Test 3: Kontoänderung
1. Ändern Sie die E-Mail-Adresse oder das Passwort eines Benutzers
2. Überprüfen Sie den E-Mail-Posteingang
3. ✅ Wenn deaktiviert: Keine Bestätigungs-E-Mail
4. ❌ Wenn aktiv: Bestätigungs-E-Mail erhalten

## Deinstallation

### Plugin deaktivieren (Einstellungen bleiben erhalten)
1. Gehen Sie zu **Plugins → Installierte Plugins**
2. Klicken Sie bei "Wegwandern Email Control" auf **Deaktivieren**
3. E-Mails werden wieder normal versendet
4. Einstellungen bleiben in der Datenbank gespeichert

### Plugin komplett entfernen
1. Deaktivieren Sie das Plugin
2. Klicken Sie auf **Löschen**
3. Alle Einstellungen werden aus der Datenbank entfernt
4. Der Plugin-Ordner wird gelöscht

## Fehlerbehebung

### Problem: E-Mails werden immer noch gesendet

**Lösung 1**: Plugin-Cache leeren
- Deaktivieren Sie das Plugin
- Warten Sie 30 Sekunden
- Aktivieren Sie das Plugin wieder

**Lösung 2**: Einstellungen überprüfen
- Gehen Sie zu Einstellungen → Email Control
- Stellen Sie sicher, dass die richtigen Checkboxen aktiviert sind
- Klicken Sie auf "Einstellungen speichern"

**Lösung 3**: Andere Plugins prüfen
- Andere E-Mail-Plugins (z.B. WP Mail SMTP) könnten die Einstellungen überschreiben
- Deaktivieren Sie temporär andere E-Mail-bezogene Plugins zum Testen

### Problem: Wichtige E-Mails werden nicht gesendet

**Lösung**: Selektive Deaktivierung
- Aktivieren Sie nur die E-Mail-Typen, die Sie wirklich deaktivieren möchten
- Lassen Sie wichtige E-Mails (z.B. Passwort-Reset an Benutzer) aktiv

### Problem: Plugin erscheint nicht im Menü

**Lösung**: Berechtigungen prüfen
- Nur Benutzer mit "manage_options" Berechtigung können die Einstellungen sehen
- Stellen Sie sicher, dass Sie als Administrator angemeldet sind

## Support

Bei weiteren Fragen oder Problemen:
- **Website**: https://www.pitsolutions.ch/
- **E-Mail**: info@pitsolutions.ch


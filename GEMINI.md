# Projektregler & Riktlinjer för Moderaterna i Ale (ale.nu)

Detta dokument innehåller tvingande instruktioner och kvalitetskrav för AI-assistenten vid allt arbete i denna kodbas.

---

## 1. Strikt Faktaintegritet & Källdata (Nolltolerans mot gissningar / hallucinationer)
- **Hitta aldrig på personuppgifter:** Namn, åldrar, yrkestitlar, placeringar på valsedlar och roller får ALDRIG gissas, hårdkodas ur minnet eller genereras hypotetiskt.
- **Källor för valsedlar och kandidater:** All kandidatinformation ska ALLTID hämtas direkt från de officiella listorna i `folkvalda.html` och `scratch/build_candidates.py`.
- **Källor för insändare och artiklar:** Alla texter, citat, publiceringsveckor och författare ska ALLTID läsas in och parsas direkt från originalfilerna i `material/insandare/*.docx`.
- **Om källdata saknas:** Fråga alltid användaren eller undersök projektets filer innan text skapas.

---

## 2. Teckenkodning & Svenska tecken (Å, Ä, Ö)
- **UTF-8 i alla filer:** Alla skapade och redigerade filer (.html, .py, .txt, .md, .css) ska skrivas med explicit UTF-8-kodning.
- **Textfiler för webb/Windows:** Filer av typen `.txt` (såsom `llms.txt`) ska sparas med `utf-8-sig` (UTF-8 med BOM) så att webbläsare och Windows-verktyg alltid visar `å`, `ä`, `ö` korrekt utan mojibake (`Ã¥`, `Ã¤`, `Ã¶`).

---

## 3. Verifiering och Kontroll före leverans
- **Dubbelkolla mot originaldata:** Innan en genererad fil eller funktion rapporteras som klar, ska data stickprovas eller verifieras med skript mot originalkällan.
- **Konsistens mellan sidor:** När menyer, sidhuvuden, sidfötter eller kontaktuppgifter ändras ska ändringen appliceras synkront över samtliga sidor (`index.html`, `kvalitet.html`, `utveckling.html`, `livsmiljo.html`, `insandare.html`, `folkvalda.html`).

---

## 4. Git och versionshantering
- Skapa rena, tydliga commits efter varje slutförd arbetsuppgift.
- Inkludera inte temporära låsfiler (`~$*`) eller systemfiler (`Thumbs.db`) i commits.

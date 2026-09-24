# Stageproject — UrenSync: ClockWise-uren naar Syntess

Voor: Wasim · Opdrachtgever en begeleider: Ayoub

Dit is je examenproject. Je bouwt een eigen applicatie, van ontwerp tot demo,
en alle acht opdrachten van kerntaak 1 en 2 komen uit dit ene project. Het is
een echt probleem van een echte klant, maar je werkt de hele stage met
**verzonnen data** en een **nagebouwde Syntess-database** (de "nep-Atrium",
zie deze repo). Je bouwt dus de hele keten zelf, ook het
schrijven naar Syntess. Alleen het aansluiten op het echte systeem van de
klant doet Ayoub na oplevering. Jij kunt dus niets kapotmaken.

## 1. Het probleem

Installatiebedrijf Elmar Services registreert uren in **ClockWise**
(urenregistratie in de browser). De administratie werkt in **Syntess Atrium**,
het ERP-pakket waarin werkbonnen, projecten en facturen staan. Die twee praten
niet met elkaar. Elke week typt iemand de uren uit ClockWise over in Syntess.
Dat kost tijd, gaat fout, en uren worden soms vergeten en dus nooit
gefactureerd.

## 2. Wat je gaat bouwen

**UrenSync**: een webapp die urenregels uit ClockWise inleest, ze vertaalt naar
de begrippen van Syntess (welke medewerker, welk project, welke urensoort) en
ze doorstuurt naar Syntess. Wat hij niet kan vertalen zet hij in een wachtrij
waar de administratie het handmatig oplost. Elke run levert een rapport.

Eindresultaat waar de opdrachtgever akkoord op geeft:

1. Een export van ClockWise (xlsx) kan worden ge-upload of ingelezen.
2. Elke regel wordt automatisch gemapt of komt in de wachtrij.
3. Gemapte regels worden als urenregels in de (nep-)Syntess-database geschreven, precies zoals Syntess het zelf zou doen (zie hoofdstuk 6). Dezelfde regel wordt nooit twee keer geschreven.
4. Beheerscherm: mapping-tabellen bijhouden, wachtrij afhandelen, rapport per run bekijken.
5. Dry-run: een run die alles doet behalve versturen, zodat je vooraf ziet wat er zou gebeuren.

### Wat er NIET in zit

- De echte ClockWise-API. Je leest het xlsx-bestand. (Als Elmar tijdens de stage een API-sleutel geeft, is de API een verbetervoorstel in opdracht 5, geen eis.)
- De echte Syntess-database van de klant. Je schrijft naar de nep-Atrium uit `urensync-starter`, die dezelfde tabellen heeft.
- Echte namen of uren van Elmar-medewerkers. Nooit. Zie hoofdstuk 7.
- Facturatie, urenlogboek voor medewerkers, mobiele app.

## 3. Eisen en wensen (MoSCoW)

Dit is de lijst van de opdrachtgever. In opdracht 1 en 2 ga je hierover
doorvragen; sommige punten zijn expres onduidelijk gelaten.

**Must**

- Xlsx-export inlezen in het formaat van hoofdstuk 5. Foute bestanden geven een nette foutmelding, geen crash.
- Mapping-tabellen: medewerker → Syntess-medewerkernummer, project → Syntess-projectnummer, activiteit → Syntess-urensoort.
- Regels zonder complete mapping komen in de wachtrij, met de reden.
- Vanuit de wachtrij kun je een mapping toevoegen en de regel opnieuw laten verwerken.
- Elke ClockWise-regel krijgt een eigen sleutel (datum + gebruiker + project + tijd + toelichting, of beter als jij iets beters bedenkt). Een regel die al geschreven is, wordt overgeslagen. Syntess heeft geen kolom voor jouw sleutel, dus die administratie hou je in je eigen database bij, samen met het Syntess-ID dat de regel kreeg.
- Een week die in Syntess al naar de salarisadministratie is (`GEEXPORTEERD_JN = 'J'`) krijgt geen nieuwe regels meer. Die gaan naar de wachtrij.
- Medewerkers die uit dienst zijn en projecten die zijn afgesloten (`GC_HISTORISCH_JN = 'J'`) krijgen geen uren.
- Rapport per run: aantal gelezen, verstuurd, in wachtrij, fout, overgeslagen.
- Dry-run.
- Inloggen. Alleen ingelogde beheerders komen erin.

**Should**

- Rapport per run ook als e-mail (mag een nep-mailer zijn die naar een logbestand schrijft).
- Filter in de wachtrij op medewerker, project, periode.
- Een regel handmatig afkeuren ("hoort niet in Syntess").

**Could**

- Een run automatisch elke nacht (cron).
- Andere richting: projecten uit Syntess automatisch als mapping-regel voorstellen.

## 4. Hoe dit past op je examenopdrachten

| Examenopdracht | Wat je in dit project doet | Bewijs |
|---|---|---|
| K1-1 Afstemmen en plannen | Schrijf de opdrachtomschrijving in je eigen woorden, deel het project in taken, plan met deadlines. Mail Ayoub met vragen over minstens één onduidelijke eis (bijvoorbeeld: wat is precies een "urensoort"? wat als één regel twee projecten bevat?). | omschrijving, planning, e-mailwisseling |
| K1-2 Technisch ontwerp | ERD van je database, use-casediagram van het beheerscherm. Onderbouw: waarom een wachtrij, waarom deze sleutel voor dedupe, hoe je omgaat met persoonsgegevens (uren van medewerkers zijn persoonsgegevens), wie er in mag. Presenteer aan Ayoub, verwerk feedback, krijg akkoord. | ontwerp, diagrammen, pitchverslag, akkoord |
| K1-3 Realiseren | Bouw het. Stack en conventies in hoofdstuk 8. Elke werkdag commits. | tools, code, commits, uitleg |
| K1-4 Testen | Testplan met testscenario's per functie: goed bestand, leeg bestand, verkeerde kolommen, dubbele regel, onbekend project, dry-run versus echte run. Vitest voor de mapping-logica, handmatige scenario's voor de schermen. | testplan, scenario's, resultaten |
| K1-5 Verbetervoorstellen | Na de demo: wat vond Ayoub, wat kwam uit de tests, wat zou de ClockWise-API opleveren. Per voorstel: wat, hoe lang, welke stappen. | voorstellen, planning, afspraken |
| K2-1 Samenwerken | Het wekelijkse overleg met Ayoub (en Hassan als hij er is). Neem er één op. | opname, reflectie, afspraken |
| K2-2 Presenteren | Demo van UrenSync aan het eind, opgenomen. | video |
| K2-3 Evalueren | Evaluatiegesprek na de demo, opgenomen. | audio |

## 5. Het invoerbestand

ClockWise exporteert een xlsx "Overzicht met begin- en eindtijden". Zo ziet
het eruit (kolom A is altijd leeg, de data begint op rij 6):

| rij | inhoud |
|---|---|
| 1 | `Overzicht met begin- en eindtijden` |
| 2 | `Begindatum: 1-1-2023` |
| 3 | `Einddatum: 31-8-2026` |
| 4 | leeg |
| 5 | kolomkoppen |
| 6 en verder | data |
| laatste | een regel die begint met `Totaal:` (negeren) |

Kolommen vanaf B:

| Kolom | Voorbeeld | Opmerking |
|---|---|---|
| Datum | `31-8-2026` | tekst, Nederlands formaat dag-maand-jaar |
| Gebruikersnaam | `Jan de Vries` | de medewerker |
| Begin- en eindtijd | `09:00 - 12:30` of `Niet gespecificeerd` | |
| Uren | `2` | heel getal |
| Minuten | `30` | heel getal, 0 tot 59 |
| Project | `1. Elmar Services`, `1.1.Elmar Internationaal `, `5.1. Het Zorg Kompas` | let op: spaties aan het eind komen voor, het nummer voor de punt is het projectnummer |
| Activiteit | `Niet gespecificeerd`, `IT-Werkzaamheden` | |
| Toelichting | vrije tekst of leeg | |

Dingen om over na te denken (en over te mailen): uren en minuten zijn twee
kolommen, Syntess wil één getal. "Niet gespecificeerd" is geen activiteit.
Eén persoon kan op één dag vijf regels hebben op hetzelfde project.

## 6. Schrijven naar Syntess

Syntess Atrium bewaart uren in een Firebird-database. In `urensync-starter`
staat een nagebouwde versie met dezelfde tabellen (`AT_MEDEW`, `AT_WERK`,
`AT_TAAK`, `AT_URENPER`, `AT_DOCUMENT`, `AT_URENSTAT`, `AT_URENBREG`) en
verzonnen data. Lees `docs/nep-atrium.md` voordat je aan het
ontwerp begint: daar staat hoe de tabellen samenhangen, in welke volgorde je
moet schrijven (periode → document → koppelregel → urenregel, in één
transactie) en welke valkuilen er zijn.

Twee regels:

1. **Alle Syntess-code in één module**, bijvoorbeeld `src/lib/syntess/`. De
   rest van je app kent Syntess alleen via een interface, ongeveer zo:

   ```ts
   interface SyntessAdapter {
     haalMedewerkers(): Promise<SyntessMedewerker[]>;
     haalProjecten(): Promise<SyntessProject[]>;
     haalTaken(): Promise<SyntessTaak[]>;
     schrijfUrenregel(regel: SyntessUrenregel, opties: { dryRun: boolean }): Promise<SyntessSchrijfResultaat>;
   }
   ```

   Zo kan Ayoub na je stage dezelfde module op het echte systeem zetten, en
   kun jij in je tests een nep-adapter gebruiken die niets schrijft.

2. **Alle klantspecifieke sleutels in configuratie** (`.env` of een
   instellingentabel), nooit in code. Welke dat zijn staat in `nep-atrium.md`,
   hoofdstuk 3. Bij de echte klant zijn ze anders.

Wat `schrijfUrenregel` minimaal teruggeeft: het `AT_URENBREG.GC_ID` dat de
regel kreeg, of de reden waarom er niet geschreven is (week al geëxporteerd,
medewerker onbekend, project afgesloten). Die reden komt in de wachtrij.

## 7. Testdata en privacy

Je krijgt **geen** echte export. Je maakt er zelf een met een script
(`scripts/maak-testdata.ts`) dat een xlsx genereert in het formaat van
hoofdstuk 5, met:

- de medewerkers en klanten uit de nep-Atrium (`firebird/02-seed.sql`), maar dan zoals ze in ClockWise zouden heten: `1. Bakkerij Jansen`, `1.1. Bakkerij Jansen Zuid`, `2. Sporthal De Kuil`, enzovoort. Plus één medewerker en één project die in Atrium níet bestaan;
- 3 maanden data, zo'n 600 regels;
- bewust vervuilde regels: spaties achter projectnamen, "Niet gespecificeerd", een project dat niet in je mapping staat, twee identieke regels, een regel met 0 uur, een regel met Minuten = 75;
- een `Totaal:`-regel onderaan.

Dat script is onderdeel van je oplevering: het is je testset én het bewijs
dat je met persoonsgegevens hebt nagedacht. In je ontwerp (opdracht 2) leg je
uit waarom je nooit echte namen op je laptop hebt gehad en wat er in de echte
app extra nodig is (wie mag de wachtrij zien, hoe lang bewaar je runs).

## 8. Stack en conventies

Zelfde stack als ITFlow, zodat wat je in week 1 en 2 leerde direct van pas komt.

| Laag | Keuze |
|---|---|
| Framework | Next.js (App Router), React, TypeScript, geen `any` |
| UI | Tailwind + shadcn/ui |
| Eigen data | Prisma op Postgres, lokaal in Docker (staat in `docker-compose.yml`) |
| Syntess | Firebird 3 via `node-firebird` (of, als je liever .NET doet zoals Clockd: `FirebirdSql.Data.FirebirdClient` + Dapper) |
| Validatie | Zod, één schema per API-route, gedeeld met het formulier |
| Xlsx lezen en schrijven | `exceljs` of `xlsx` (SheetJS); kies er één en leg in opdracht 3 uit waarom |
| Auth | next-auth, één rol "beheerder" |
| Tests | Vitest voor `src/lib/**` (parser, mapping, dedupe-sleutel) |
| Repo | eigen GitHub-repo `urensync`, Ayoub als collaborator, branch per taak, PR naar `main` |

Conventies:

- Bestanden en mappen in kebab-case, componenten in PascalCase, functies en variabelen in camelCase, Prisma-modellen in PascalCase enkelvoud (`UrenRegel`, niet `urenregels`).
- Nederlandse namen voor domeinbegrippen (`urensoort`, `wachtrij`), Engelse voor techniek (`route.ts`, `useQuery`).
- Commitboodschappen: kort, in de gebiedende wijs, zeggen wat en waarom: `Parser: sla Totaal-regel over`.
- `npm run lint` en `npx vitest run` zonder fouten voordat je een PR opent.
- Logica staat in `src/lib`, niet in componenten en niet in route handlers. Route handlers valideren, roepen `src/lib` aan, en geven antwoord.

## 9. Fasering

Geen dagen zoals in week 1, maar fases met een oplevermoment. Hoeveel dagen
per fase spreek je zelf af in je planning (opdracht 1). De volgorde ligt vast.

| Fase | Oplevering | Examenopdracht |
|---|---|---|
| 0 Afstemmen | opdrachtomschrijving, projectbord met epics en stories (zie `docs/WERKWIJZE.md`), sprintplanning, mailwisseling | K1-1 |
| 1 Ontwerp | ERD, use-cases, onderbouwing, pitch aan Ayoub, akkoord | K1-2 |
| 2 Fundament | repo, nep-Atrium draait, Prisma-schema, login, testdata-script werkt | K1-3 |
| 3 Inlezen en mappen | parser met tests, mapping-tabellen met beheerscherm, wachtrij | K1-3 |
| 4 Schrijven | Syntess-module (document → urenstat → urenbreg), dedupe, dry-run, rapport per run | K1-3 |
| 5 Testen | testplan uitgevoerd, bugs gefixt | K1-4 |
| 6 Opleveren | demo (video), verbetervoorstellen, evaluatie | K1-5, K2-2, K2-3 |

Het wekelijks overleg loopt de hele tijd door (K2-1). Kies zelf welke week
je opneemt; een week waarin iets misging is interessanter dan een week
waarin alles klopte.

## 10. Werkafspraken

- Je bent er elke **maandag en dinsdag**. Maandag begint met een kort overleg met Ayoub: wat is af, waar zit je vast, wat ga je deze week doen. Dinsdag aan het eind van de dag laat je zien wat er staat. Schrijf de afspraken na afloop in `docs/overleg.md`.
- Sprints duren twee weken, dus vier werkdagen. Sprintplanning en review vallen op de maandag van de even week.
- Zit je langer dan een uur vast: vraag het. Via chat, met wat je al geprobeerd hebt.
- AI-hulp mag. Regel blijft: elke regel die je commit kun je uitleggen.
- Loopt je planning uit: zeg het in het overleg, niet erna. Samen schrappen we een "should" of een "could". De "musts" blijven.

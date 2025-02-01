<?php

namespace App\Services;

use Http;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Modele;
use App\Models\Livret;
use App\Models\User;
use App\Models\Groupe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use stdClass;

class PdfGenerator extends Fpdi
{
    private Livret $livret;
    private Modele $modele;

    public function __construct(Livret $livret, Modele $modele)
    {
        parent::__construct();
        $this->livret = $livret;
        $this->modele = $modele;
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $groupeNom = $this->modele->groupe->nom ?? 'Groupe Inconnu';
        $this->Cell(0, 10, 'LIVRET D\'APPRENTISSAGE / ' . $groupeNom, 0, 0, 'L');
    }

    public function generate(): void
    {
        $this->setPrintHeader(false);
        $this->setTitle('Livret d\'apprentissage');
        $this->AddPage();

        // Logo et première page
        $imagePath = public_path('img/logo_garde.png');

        $this->Image($imagePath, 10, 10, 190, 44);


        $htmlStartY = 60;

        $html = view('pdf.header', [
            'modele' => $this->modele,
            'livret' => $this->livret
        ])->render();
        $this->writeHTMLCell(0, 10, 10, $htmlStartY, $html);

        $this->importAdditionalPdfs();
        $this->addDynamicContent();

        $directory = 'livrets';

    // Vérifiez si le répertoire existe sur le disque public
    if (!Storage::disk('public')->exists($directory)) {
        Storage::disk('public')->makeDirectory($directory);
    }

    // Chemin relatif pour le fichier PDF
    $filePath = $directory . '/livret_' . $this->livret->user->nom . '_' . $this->livret->user->prenom . '.pdf';

    // Génération et enregistrement du fichier PDF
    $fullPath = Storage::disk('public')->path($filePath);
    $this->Output($fullPath, 'F');
    }

    public function importAdditionalPdfs(Collection $pdfCompositions = null): void
    {
        if ($pdfCompositions === null) {
            $pdfCompositions = DB::table('compositions')
                ->where('modele_id', $this->modele->id)
                ->get();
            }

        foreach ($pdfCompositions as $composition) {
            if (!isset($composition->lien)) {
                continue;
            }
            $compositionLien = is_string($composition->lien) ? $composition->lien : "";

            $url = 'https://raw.githubusercontent.com/bastienchevalier5/CarnApprenti-Administration/master/CarnApprenti/wwwroot/' . $compositionLien;
            $response = Http::get($url);

            if ($response->successful()) {
                $pdfContent = $response->body();
                $filePath = storage_path('app/public/' . $compositionLien);
                file_put_contents($filePath, $pdfContent);

                try {
                    $pageCount = $this->setSourceFile($filePath);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $this->importPage($pageNo);
                        $size = $this->getTemplateSize($templateId);

                        if (is_array($size)) {
                            $orientation = is_string($size['orientation']) ? $size['orientation'] : "P";
                            $this->AddPage(
                                $orientation,
                                [$size['width'] ?? 210, $size['height'] ?? 297]
                            );
                            $this->useTemplate($templateId);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing PDF file: ' . $e->getMessage());
                }
            } else {
                Log::error('Failed to download PDF from GitHub: ' . $response->status());
            }
        }
    }

    public function addDynamicContent(): void
    {
        $this->AddPage();
        $this->SetFont('helvetica', '', 10);

        $personnels = DB::table('personnels')->get();
        $htmlContent = view('pdf.personnel', ['personnels' => $personnels])->render();
        $this->writeHTML($htmlContent, true, false, true, false, '');

        $this->addEquipePedagogique();
        $this->addReports();
        $this->addObservations();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function LoadData(int $id_groupe): Collection
    {
        return DB::table('groupe_matiere')
            ->join('matieres', 'groupe_matiere.matiere_id', '=', 'matieres.id')
            ->join('formateurs', 'matieres.formateur_id', '=', 'formateurs.id')
            ->where('groupe_matiere.groupe_id', $id_groupe)
            ->select('matieres.nom as nom_matiere', 'formateurs.prenom', 'formateurs.nom')
            ->get();
    }

    /**
     * @param array<string> $header
     * @param Collection<int, stdClass> $data
     * @param array<int, float> $w
     */
    public function ColoredTable(array $header, Collection $data, array $w): void
    {
        $this->SetLeftMargin(17);
        $this->SetRightMargin(17);

        $lmargin = is_numeric($this->lMargin) ? $this->lMargin : 0;
        $rmargin = is_numeric($this->rMargin) ? $this->rMargin : 0;
        $pageWidth = $this->GetPageWidth() - $lmargin - $rmargin;
        $w = [$pageWidth * 0.5, $pageWidth * 0.5];

        $tableWidth = array_sum($w);
        $xStart = ($pageWidth - $tableWidth) / 2 + $lmargin;

        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetFont('', 'B');

        $this->SetX($xStart);

        foreach ($header as $i => $columnHeader) {
            $this->Cell($w[$i], 7, $columnHeader, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');

        $fill = false;
        foreach ($data as $row) {
            $this->SetX($xStart);
            $nomMatiere = is_string($row->nom_matiere) ? $row->nom_matiere : '';
            $this->Cell($w[0], 9, $nomMatiere, 'LR', 0, 'C', $fill);
            $prenom = is_string($row->prenom) ? $row->prenom : '';
            $nom = is_string($row->nom) ? $row->nom : '';
            $formattedName = $prenom . ' ' . $nom;
                        $this->Cell($w[1], 9, $formattedName, 'LR', 0, 'C', $fill);
            $this->Ln();
            $fill = !$fill;
        }

        $this->SetX($xStart);
        $this->Cell($tableWidth, 0, '', 'T');
    }
    /**
     * Summary of addEquipePedagogique
     * @return void
     */
    public function addEquipePedagogique(): void
    {
        $this->AddPage();
        $this->SetFont('', 'B', 15);
        $this->SetTextColor(60, 63, 235);

        $groupeNom = $this->modele->groupe->nom ?? 'Groupe Inconnu';
        $this->Write(10, 'EQUIPE PEDAGOGIQUE - ' . $groupeNom, '', false, 'L');
        $this->Ln(20);

        $this->SetFont('', '', 10);
        $header = ['Matière', 'Formateur'];

        $groupeId = $this->modele->groupe?->id;

        if (!is_int($groupeId)) {
            $groupeId = 0; // Default fallback value.
        }

        $data = $this->LoadData($groupeId);

        $w = [90, 100];
        $this->ColoredTable($header, $data, $w);
        $this->Ln(15);
    }
    /**
     * Summary of addReports
     * @return void
     */
    /**
 * @param stdClass $report
 * @return string
 */
public function validateReportField($report, string $field): string
{
    if (!property_exists($report, $field)) {
        return 'Invalid data';
    }

    return is_string($report->$field) || is_null($report->$field)
        ? (string)($report->$field ?? '')
        : 'Invalid data';
}

public function addReports(): void
{
    /** @var Collection<int, stdClass> */
    $reports = DB::table('compte_rendus')
        ->where('livret_id', $this->livret->id)
        ->get();

    foreach ($reports as $report) {
        $this->AddPage();

        // Get user data through the relationship
        /** @var User|null */
        $user = $this->livret->user;
        $userName = $user->nom ?? 'Invalid data';
        $userPrenom = $user->prenom ?? 'Invalid data';

        // Header section
        $this->WriteHTML('<strong>Nom - Prénom : </strong>' . htmlspecialchars($userName) . ' ' . htmlspecialchars($userPrenom));
        $this->Ln(10);

        // Period section
        $periode = $this->validateReportField($report, 'periode');
        $this->WriteHTML('<strong>Période : </strong>' . htmlspecialchars($periode));
        $this->Ln(15);

        $this->WriteHTML("<strong>Compte-Rendu d'Activités en Entreprise</strong>", true, false, false, false, 'C');
        $this->Ln(15);

        // Activities and observations
        $sections = [
            [
                'title' => 'Activités professionnelles confiées en entreprise',
                'content' => $this->validateReportField($report, 'activites_pro'),
                'height' => 75
            ],
            [
                'title' => 'Observations de l\'apprenti',
                'content' => $this->validateReportField($report, 'observations_apprenti'),
                'height' => 52
            ],
            [
                'title' => 'Observations du tuteur',
                'content' => $this->validateReportField($report, 'observations_tuteur'),
                'height' => 35
            ],
            [
                'title' => 'Observations du référent du groupe',
                'content' => $this->validateReportField($report, 'observations_referent'),
                'height' => 51
            ]
        ];

        foreach ($sections as $section) {
            $content = '<strong>' . htmlspecialchars($section['title']) . '</strong><br><br>' .
                      htmlspecialchars($section['content']);

            $this->MultiCell(
                0,
                $section['height'],
                $content,
                'LTRB',
                'L',
                false,
                1,
                null,
                null,
                true,
                0,
                true
            );
        }
    }
}


    /**
     * Summary of addObservations
     * @return void
     */
    public function addObservations(): void
{
    $observations = DB::table('livrets')
        ->select('observation_apprenti_global', 'observation_admin')
        ->where('id', $this->livret->id)
        ->first();

    $this->AddPage();

    if ($observations) {
        $observationApprentiGlobal = data_get($observations, 'observation_apprenti_global');
        $observationAdmin = data_get($observations, 'observation_admin');

        $observationApprentiGlobal = is_string($observationApprentiGlobal) || is_null($observationApprentiGlobal)
            ? (string)($observationApprentiGlobal ?? '')
            : 'Invalid data';

        $observationAdmin = is_string($observationAdmin) || is_null($observationAdmin)
            ? (string)($observationAdmin ?? '')
            : 'Invalid data';

        $this->MultiCell(
            0,
            75,
            '<h2 style="color:rgb(0,88,165)">OBSERVATIONS DE L\'APPRENTI</h2><br><br>' .
            $observationApprentiGlobal,
            0,
            'L',
            false,
            1,
            null,
            null,
            true,
            0,
            true
        );

        $this->MultiCell(
            0,
            75,
            '<h2 style="color:rgb(0,88,165)">OBSERVATIONS DU RESPONSABLE PEDAGOGIQUE</h2><br><br>' .
            $observationAdmin,
            0,
            'L',
            false,
            1,
            null,
            null,
            true,
            0,
            true
        );
    } else {
        $this->Write(0, 'Aucune observation disponible.');
    }
}


}

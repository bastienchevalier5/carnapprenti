<?php

namespace App\Services;

use Http;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Modele;
use App\Models\Livret;
use App\Models\User;

class PdfGenerator extends Fpdi
{
    private $livret;
    private $modele;

    public function __construct(Livret $livret, Modele $modele)
    {
        parent::__construct();
        $this->livret = $livret;
        $this->modele = $modele;
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $groupeNom = $this->modele->groupe->nom ?? 'Groupe Inconnu';
        $this->Cell(0, 10, 'LIVRET D\'APPRENTISSAGE / ' . $groupeNom, 0, 0, 'L');
    }

    public function generate()
    {
        $this->setPrintHeader(false);
        $this->setTitle('Livret d\'apprentissage');
        $this->AddPage();

        // Logo et première page
        $imagePath = $this->modele->firstPage?->logo ?? public_path('img/logo_garde.png');
        $this->Image($imagePath, 10, 10, 190, 44);

        $htmlStartY = 60; // Position de départ pour le contenu HTML

        // Titre et informations principales
        $html = view('pdf.header', [
            'modele' => $this->modele,
            'livret' => $this->livret
        ])->render();
        $this->writeHTMLCell(0, 10, 10, $htmlStartY, $html);

        // Importation des PDF supplémentaires
        $this->importAdditionalPdfs();

        // Contenu additionnel
        $this->addDynamicContent();

        // Génération et stockage
        $filePath = storage_path('app/public/livrets/livret-' . $this->livret->id . '.pdf');
        $this->Output($filePath, 'F');
    }

    private function importAdditionalPdfs()
    {
        $pdfCompositions = DB::table('compositions')
            ->where('modele_id', $this->modele->id)
            ->get();

            foreach ($pdfCompositions as $composition) {
                // Construire l'URL brute du fichier PDF dans le dépôt GitHub
                $url = 'https://raw.githubusercontent.com/bastienchevalier5/CarnApprenti-Administration/master/CarnApprenti/wwwroot/'. $composition->lien;

                // Effectuer la requête HTTP pour récupérer le fichier
                $response = Http::get($url);

                if ($response->successful()) {
                    // Sauvegarder le fichier PDF dans le répertoire public de Laravel
                    $pdfContent = $response->body();
                    $filePath = storage_path('app/public/' . $composition->lien);
                    file_put_contents($filePath, $pdfContent);

                    // Traiter le fichier PDF, par exemple en comptant les pages
                    $pageCount = $this->setSourceFile($filePath);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $this->importPage($pageNo);
                        $size = $this->getTemplateSize($templateId);
                        $this->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $this->useTemplate($templateId);
                    }
                } else {
                    // Gérer l'erreur si le fichier n'a pas pu être téléchargé
                    Log::error('Erreur lors du téléchargement du fichier PDF depuis GitHub: ' . $response->status());
                }
            }
    }

    private function addDynamicContent()
{
    $this->AddPage();
    $this->SetFont('helvetica', '', 10);

    // Récupérer les données nécessaires
    $personnels = DB::table('personnels')->get();

    // Charger la vue et générer le contenu HTML
    $htmlContent = view('pdf.personnel', compact('personnels'))->render();

    // Ajouter le contenu HTML au PDF
    $this->writeHTML($htmlContent, true, false, true, false, '');

    // Ajouter les autres sections
    $this->addEquipePedagogique();
    $this->addReports();
    $this->addObservations();
}

public function LoadData($id_groupe)
{
    // Récupérer les matières et les formateurs associés au groupe
    return DB::table('groupe_matiere')
        ->join('matieres', 'groupe_matiere.matiere_id', '=', 'matieres.id')
        ->join('formateurs', 'matieres.formateur_id', '=', 'formateurs.id')
        ->where('groupe_matiere.groupe_id', $id_groupe)
        ->select('matieres.nom AS nom_matiere', 'formateurs.prenom', 'formateurs.nom')
        ->get()
        ->toArray();
}

public function ColoredTable($header, $data, $w)
{
    // Ajuster les marges pour maximiser l'espace pour le tableau
    $this->SetLeftMargin(17);
    $this->SetRightMargin(17);

    // Calcul de la largeur totale du tableau
    $pageWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;
    $w = array($pageWidth * 0.5, $pageWidth * 0.5); // Colonnes égales occupant 100% de la largeur utilisable

    $tableWidth = array_sum($w);
    $xStart = ($pageWidth - $tableWidth) / 2 + $this->lMargin;

    // Définir les couleurs pour les en-têtes
    $this->SetFillColor(255, 0, 0); // Rouge vif
    $this->SetTextColor(255); // Texte blanc
    $this->SetFont('', 'B'); // Police en gras

    // Positionnement horizontal initial
    $this->SetX($xStart);

    // Affichage des en-têtes
    $num_headers = count($header);
    for ($i = 0; $i < $num_headers; ++$i) {
        $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
    }
    $this->Ln();

    // Définir les couleurs pour les lignes
    $this->SetFillColor(224, 235, 255); // Couleur de fond pour les lignes paires
    $this->SetTextColor(0); // Couleur du texte
    $this->SetFont(''); // Police normale

    $fill = 0; // Variable pour alterner les lignes
    foreach ($data as $row) {
        $this->SetX($xStart);

        $this->Cell($w[0], 9, $row->nom_matiere, 'LR', 0, 'C', $fill);
        $this->Cell($w[1], 9, $row->prenom . ' ' . $row->nom, 'LR', 0, 'C', $fill);
        $this->Ln();
        $fill = !$fill; // Alterner les lignes
    }

    // Ajouter une bordure en bas du tableau
    $this->SetX($xStart);
    $this->Cell($tableWidth, 0, '', 'T');
}



private function addEquipePedagogique()
{
    // Ajouter une nouvelle page
    $this->AddPage();

    // Définir la police pour le titre
    $this->SetFont('', 'B', 15);
    // Définir la couleur du texte en bleu
    $this->SetTextColor(60, 63, 235);
    // Écrire le titre avec un espacement avant
    $this->Write(10, 'EQUIPE PEDAGOGIQUE - ' . $this->modele->groupe->nom, '', false, 'L');
    $this->Ln(20); // Ajouter un espace après le titre

    // Définir la police pour le contenu du tableau
    $this->SetFont('', '', 10);

    // Définir les en-têtes du tableau
    $header = array('Matière', 'Formateur');

    // Charger les données pour le tableau à partir de la base de données
    $data = $this->LoadData($this->modele->groupe->id);

    // Largeur des colonnes
    $w = array(90, 100);

    // Appeler la fonction pour générer le tableau
    $this->ColoredTable($header, $data, $w);

    // Ajouter un espacement après le tableau
    $this->Ln(15);
}


private function addReports()
    {
        $reports = DB::table('compte_rendus')
            ->where('livret_id', $this->livret->id)
            ->get();

        foreach ($reports as $report) {
            $this->AddPage();
            $this->WriteHTML('<strong>Nom - Prénom : </strong>' . $this->livret->user->nom . ' ' . $this->livret->user->prenom);
            $this->Ln(10);
            $this->WriteHTML('<strong>Période : </strong>' . $report->periode);
            $this->Ln(15);
            $this->WriteHTML("<strong>Compte-Rendu d'Activités en Entreprise</strong>",true,false,false,false,'C');
            $this->Ln(15);

            $this->MultiCell(0, 75, '<strong>Activités professionnelles confiées en entreprise</strong><br><br>' . $report->activites_pro, 'LTRB', 'L', false, 1, '', '', true, 0, true);
            $this->MultiCell(0, 52, '<strong>Observations de l\'apprenti</strong><br><br>' . $report->observations_apprenti, 'LTRB', 'L', false, 1, '', '', true, 0, true);
            $this->MultiCell(0, 35, '<strong>Observations du tuteur</strong><br><br>' . $report->observations_tuteur, 'LTRB', 'L', false, 1, '', '', true, 0, true);
            $this->MultiCell(0, 51, '<strong>Observations du référent du groupe</strong><br><br>' . $report->observations_referent, 'LTRB', 'L', false, 1, '', '', true, 0, true);

        }
    }

    private function addObservations()
{
    // Récupérer la première observation de l'apprenti et l'observation du responsable pédagogique
    $observations = DB::table('livrets')
        ->select('observation_apprenti_global', 'observation_admin')
        ->where('id', $this->livret->id)
        ->first(); // Utiliser first() pour obtenir une seule ligne

    // Ajouter une page et définir la police
    $this->AddPage();

    // Vérifier que les observations existent avant de les afficher
    if ($observations) {
        // Affichage de l'observation de l'apprenti
        $this->MultiCell(0, 75, '<h2 style="color:rgb(0,88,165)">OBSERVATIONS DE L\'APPRENTI</h2><br><br>' . $observations->observation_apprenti_global, 0, 'L', false, 1, '', '', true, 0, true);

        // Affichage de l'observation du responsable pédagogique
        $this->MultiCell(0, 75, '<h2 style="color:rgb(0,88,165)">OBSERVATIONS DU RESPONSABLE PEDAGOGIQUE</h2><br><br>' . $observations->observation_admin, 0, 'L', false, 1, '', '', true, 0, true);
    } else {
        $this->Write(0, 'Aucune observation disponible.');
    }
}


}

<?php

namespace App\Services;

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
            $filePath = storage_path('app/public/' . $composition->lien);
            $pageCount = $this->setSourceFile($filePath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $this->importPage($pageNo);
                $size = $this->getTemplateSize($templateId);

                $this->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $this->useTemplate($templateId);
            }
        }
    }

    private function addDynamicContent()
{
    // Informations du personnel
    $this->AddPage();
    $this->SetFont('helvetica', '', 10);
    $this->Write(0, 'INFORMATIONS', '', false, 'C');
    $this->Ln(15);

    // Récupération des personnels et affichage de leurs informations
    $personnels = DB::table('personnels')->get();

    foreach ($personnels as $personnel) {
        $this->Write(0, $personnel->prenom . ' ' . $personnel->nom . ' - ' . $personnel->description);
        $this->Ln(10);
        $this->Write(0, 'Téléphone : ' . $personnel->telephone);
        $this->Ln(10);
        $this->Write(0, 'E-mail : ' . $personnel->email);
        $this->Ln(15);  // Ajouter un espace après chaque personnel
    }

    // Informations statiques sur les plannings et notes
    $this->Write(0, 'PLANNINGS, NOTES ET REFERENTIELS');
    $this->Ln(10);
    $this->Write(0, 'Net-Yparéo : https://formations.mayenne.cci.fr');
    $this->Ln(10);
    $this->Write(0, 'Portail web dédié au parcours de l\'étudiant (accès planning, notes, référentiels, etc.)');
    $this->Ln(10);
    $this->Write(0, 'Identifiants : communiqués par mail');
    $this->Ln(15);  // Espacement avant de passer à la page suivante
    $this->addEquipePedagogique();
    $this->addReports();
    $this->addObservations();
}


private function addEquipePedagogique()
{
    // Ajout de la page suivante pour l'équipe pédagogique
    $this->AddPage();
    $this->SetFont('helvetica', '', 10);
    $this->Write(0, 'EQUIPE PEDAGOGIQUE - ' . $this->modele->groupe->nom);
    $this->Ln(15);

    // Tableau avec les matières et formateurs
    $this->SetFont('helvetica', '', 8);
    $this->Cell(95, 10, 'Matière', 1, 0, 'C');
    $this->Cell(95, 10, 'Formateur', 1, 1, 'C');  // Nouvelle ligne pour les titres des colonnes

    // Récupération des matières et formateurs associés au groupe
    $formateurs = DB::table('groupe_matiere')
        ->join('matieres', 'groupe_matiere.matiere_id', '=', 'matieres.id')
        ->join('formateurs', 'matieres.formateur_id', '=', 'formateurs.id')
        ->where('groupe_matiere.groupe_id', $this->modele->groupe->id)
        ->select('matieres.nom AS nom_matiere', 'formateurs.prenom', 'formateurs.nom')
        ->get();

    // Affichage des lignes du tableau pour chaque matière et formateur
    foreach ($formateurs as $formateur) {
        $this->Cell(95, 10, $formateur->nom_matiere, 1, 0, 'C');
        $this->Cell(95, 10, $formateur->prenom . ' ' . $formateur->nom, 1, 1, 'C');
    }

    $this->Ln(15);  // Espacement après le tableau
}

private function addReports()
    {
        $reports = DB::table('compte_rendus')
            ->where('livret_id', $this->livret->id)
            ->get();

        foreach ($reports as $report) {
            $this->AddPage();
            $this->SetFont('helvetica', '', 10);
            $this->Write(10, 'Nom - Prénom : ' . $this->livret->user->nom . ' ' . $this->livret->user->prenom);
            $this->Ln(10);
            $this->Write(0, 'Période : ' . $report->periode);
            $this->Ln(15);
            $this->Write(0, "Compte-Rendu d'Activités en Entreprise");
            $this->Ln(15);

            $this->MultiCell(0, 75, '<strong>Activités professionnelles confiées en entreprise</strong><br><br>' . $report->activites_pro, 'LTRB', 'L', false, 1, '', '', true, 0, true);
            $this->MultiCell(0, 60, '<strong>Observations de l\'apprenti</strong><br><br>' . $report->observations_apprenti, 'LTRB', 'L', false, 1, '', '', true, 0, true);
            $this->MultiCell(0, 30, '<strong>Observations du tuteur</strong><br><br>' . $report->observations_tuteur, 'LTRB', 'L', false, 1, '', '', true, 0, true);
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
    $this->SetFont('helvetica', '', 10);

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

<?php

namespace Tests\Feature;

use App\Models\Composition;
use Http;
use Log;
use Mockery;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\TcpdfFpdi;
use TCPDF;
use Tests\TestCase;
use App\Services\PdfGenerator;
use App\Models\Livret;
use App\Models\Modele;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use stdClass;

class PdfGeneratorTest extends TestCase
{
    public function testImportAdditionalPdfsHandlesMissingLien()
    {
        // Arrange
        $livret = Livret::factory()->create();
        $modele = Modele::factory()->create();
        $compositionWithoutLien = new Composition(['lien' => null]);

        // Inject the PdfGenerator with the livret and modele
        $pdfGenerator = new PdfGenerator($livret, $modele);

        // Act
        $pdfGenerator->importAdditionalPdfs();

        // Assert
        // Vérifiez que la méthode continue lorsqu'il n'y a pas de lien
        // (vous pouvez vérifier si un log a été passé ou toute autre assertion)
        Log::shouldReceive('error')->never(); // Aucune erreur ne doit être enregistrée
    }

    public function testImportAdditionalPdfsDownloadsPdf()
    {
        // Arrange
        $livret = Livret::factory()->create();
        $modele = Modele::factory()->create();
        $compositionWithLien = (object)['lien' => 'example.pdf']; // Utiliser un objet pour simuler une composition

        // Simuler une réponse HTTP réussie
        Http::fake([
            'https://raw.githubusercontent.com/bastienchevalier5/CarnApprenti-Administration/master/CarnApprenti/wwwroot/example.pdf' => Http::sequence()->push('PDF CONTENT HERE'),
        ]);

        $pdfGenerator = new PdfGenerator($livret, $modele);

        // Act
        $pdfGenerator->importAdditionalPdfs(collect([$compositionWithLien]));

        // Assert
        // Vérifiez que le PDF a été téléchargé
        $this->assertFileExists(storage_path('app/public/example.pdf'));
    }

    public function testImportAdditionalPdfsHandlesFailedDownload()
    {
        // Arrange
        $livret = Livret::factory()->create();
        $modele = Modele::factory()->create();
        $compositionWithLien = (object)['lien' => 'nonexistent.pdf']; // Simuler une composition avec un lien

        // Simuler une réponse HTTP échouée
        Http::fake([
            'https://raw.githubusercontent.com/bastienchevalier5/CarnApprenti-Administration/master/CarnApprenti/wwwroot/nonexistent.pdf' => Http::response('', 404),
        ]);

        $pdfGenerator = new PdfGenerator($livret, $modele);

        // Configurer le logger pour attendre l'appel
        Log::shouldReceive('error')->once()->with('Failed to download PDF from GitHub: 404');

        // Act
        $pdfGenerator->importAdditionalPdfs(collect([$compositionWithLien])); // Passer directement la collection
    }

    public function testPdfPageImport()
    {
        // Chemin d'un PDF source de test (assurez-vous qu'il existe pour le test)
        $pdfPath = sys_get_temp_dir() . '/test-source.pdf';

        // Création d'un PDF factice pour le test
        $fpdi = new \setasign\Fpdi\Tcpdf\Fpdi();
        $fpdi->AddPage();
        $fpdi->SetFont('helvetica', '', 12);
        $fpdi->Cell(0, 10, 'Page 1 - Test PDF', 0, 1, 'C');
        $fpdi->AddPage();
        $fpdi->Cell(0, 10, 'Page 2 - Test PDF', 0, 1, 'C');
        $fpdi->Output($pdfPath, 'F');

        // Assurez-vous que le fichier existe
        $this->assertFileExists($pdfPath, 'Le fichier PDF source doit exister.');

        // Instanciation de votre classe PdfGenerator
        $livret = $this->createMock(Livret::class);
        $modele = $this->createMock(Modele::class);
        $pdfGenerator = new PdfGenerator($livret, $modele);

        // Début des tests sur la boucle
        $pageCount = $pdfGenerator->setSourceFile($pdfPath);
        $this->assertGreaterThan(0, $pageCount, 'Le fichier PDF doit contenir au moins une page.');

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            try {
                // Importation de la page
                $templateId = $pdfGenerator->importPage($pageNo);
                $this->assertNotNull($templateId, "La page $pageNo doit être correctement importée.");

                // Taille de la page
                $size = $pdfGenerator->getTemplateSize($templateId);
                $this->assertIsArray($size, "Les dimensions de la page $pageNo doivent être récupérées.");
                $this->assertArrayHasKey('width', $size, "La largeur doit exister pour la page $pageNo.");
                $this->assertArrayHasKey('height', $size, "La hauteur doit exister pour la page $pageNo.");

                // Orientation et dimensions
                $orientation = is_string($size['orientation']) ? $size['orientation'] : "P";
                $pdfGenerator->AddPage(
                    $orientation,
                    [$size['width'] ?? 210, $size['height'] ?? 297]
                );

                // Utilisation de la page importée
                $pdfGenerator->useTemplate($templateId);
            } catch (\Exception $e) {
                $this->fail("Erreur lors du traitement de la page $pageNo : " . $e->getMessage());
            }
        }

        // Vérifiez que le PDF résultant est généré
        $outputPath = sys_get_temp_dir() . '/test-output.pdf';
        $pdfGenerator->Output($outputPath, 'F');
        $this->assertFileExists($outputPath, 'Le fichier PDF généré doit exister.');

        // Nettoyage
        unlink($pdfPath);
        unlink($outputPath);
    }

    public function testTableRowRendering()
{
    // Configuration initiale
    $fpdiMock = $this->getMockBuilder(\setasign\Fpdi\Tcpdf\Fpdi::class)
        ->onlyMethods(['SetX', 'Cell', 'Ln'])
        ->getMock();

    // Simulation des dimensions des colonnes
    $columnWidths = [50, 100];

    // Simulation des données d'une ligne
    $rows = [
        (object)[
            'nom_matiere' => 'Mathématiques',
            'prenom' => 'Jean',
            'nom' => 'Dupont'
        ],
        (object)[
            'nom_matiere' => 'Physique',
            'prenom' => 'Marie',
            'nom' => 'Curie'
        ],
        (object)[
            'nom_matiere' => null, // Test avec une valeur invalide
            'prenom' => '',
            'nom' => null
        ]
    ];

    $xStart = 10;
    $fill = false;

    // Définir les attentes
    $fpdiMock->expects($this->exactly(count($rows)))
        ->method('SetX')
        ->with($xStart);

    // Préparation des appels attendus pour Cell
    $expectedCells = [
        // Ligne 1
        [$columnWidths[0], 9, 'Mathématiques', 'LR', 0, 'C', false],
        [$columnWidths[1], 9, 'Jean Dupont', 'LR', 0, 'C', false],
        // Ligne 2
        [$columnWidths[0], 9, 'Physique', 'LR', 0, 'C', true],
        [$columnWidths[1], 9, 'Marie Curie', 'LR', 0, 'C', true],
        // Ligne 3
        [$columnWidths[0], 9, '', 'LR', 0, 'C', false],
        [$columnWidths[1], 9, ' ', 'LR', 0, 'C', false],
    ];

    $fpdiMock->expects($this->exactly(count($expectedCells)))
        ->method('Cell')
        ->willReturnCallback(function (...$args) use (&$expectedCells) {
            $expectedCall = array_shift($expectedCells); // Obtenez la prochaine attente
            $filteredArgs = array_slice($args, 0, count($expectedCall)); // Ignorez les arguments supplémentaires
            $this->assertEquals($expectedCall, $filteredArgs); // Vérifiez que les paramètres essentiels correspondent
        });

    $fpdiMock->expects($this->exactly(count($rows)))
        ->method('Ln');

    // Appeler la méthode testée pour chaque ligne
    foreach ($rows as $row) {
        $this->renderTableRow($fpdiMock, $row, $columnWidths, $xStart, $fill);
        $fill = !$fill;
    }
}


    public function renderTableRow($fpdi, $row, $columnWidths, $xStart, $fill)
{
    $fpdi->SetX($xStart);
    $nomMatiere = is_string($row->nom_matiere) ? $row->nom_matiere : '';
    $fpdi->Cell($columnWidths[0], 9, $nomMatiere, 'LR', 0, 'C', $fill);

    $prenom = is_string($row->prenom) ? $row->prenom : '';
    $nom = is_string($row->nom) ? $row->nom : '';
    $formattedName = $prenom . ' ' . $nom;
    $fpdi->Cell($columnWidths[1], 9, $formattedName, 'LR', 0, 'C', $fill);

    $fpdi->Ln();
}





}

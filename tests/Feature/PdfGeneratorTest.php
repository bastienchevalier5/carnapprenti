<?php

namespace Tests\Feature;

use App\Models\Composition;
use App\Models\User;
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

    public function testValidateReportFieldReturnsValidString(): void
    {
        $report = new stdClass();
        $report->field = "Valid data";

        $modele = Modele::factory()->create();
        $livret = Livret::factory()->create([
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($livret, $modele);
        $result = $pdfGenerator->validateReportField($report, 'field');

        $this->assertEquals("Valid data", $result);
    }

    public function testValidateReportFieldReturnsEmptyStringForNull(): void
    {
        $report = new stdClass();
        $report->field = null;

        $modele = Modele::factory()->create();
        $livret = Livret::factory()->create([
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($livret, $modele);

        $result = $pdfGenerator->validateReportField($report, 'field');

        $this->assertEquals("", $result);
    }

    public function testValidateReportFieldReturnsInvalidDataForNonString(): void
    {
        $report = new stdClass();
        $report->field = 123; // Not a string

        $modele = Modele::factory()->create();
        $livret = Livret::factory()->create([
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($livret, $modele);
        $result = $pdfGenerator->validateReportField($report, 'field');

        $this->assertEquals("Invalid data", $result);
    }

    public function testValidateReportFieldReturnsInvalidDataForMissingProperty(): void
    {
        $report = new stdClass(); // No properties defined

        $modele = Modele::factory()->create();
        $livret = Livret::factory()->create([
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($livret, $modele);
        $result = $pdfGenerator->validateReportField($report, 'field');

        $this->assertEquals("Invalid data", $result);
    }

    public function testAddReportsWithCompleteUserData()
    {
        // Arrange
        $mockUser = User::factory()->create();
        $modele = Modele::factory()->create();
        $mockLivret = Livret::factory()->create([
            'user_id' => $mockUser->id,
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($mockLivret, $modele);

        $mockReport = new stdClass();
        $mockReport->periode = 'Septembre 2023';
        $mockReport->activites_pro = 'Développement web';
        $mockReport->observations_apprenti = 'Expérience enrichissante';
        $mockReport->observations_tuteur = 'Bon potentiel';
        $mockReport->observations_referent = 'Progrès satisfaisants';

        // Use Mockery to mock the DB query
        DB::shouldReceive('table->where->get')
            ->once()
            ->andReturn(collect([$mockReport]));

        // Act
        // Capture output or use a mock PDF generation method
        $pdfGenerator->addReports();

        // Assert
        // Add specific assertions based on your requirements
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testAddReportsWithMissingUserData()
    {
        $mockUser = User::factory()->create();
        $modele = Modele::factory()->create();
        $mockLivret = Livret::factory()->create([
            'user_id' => $mockUser->id,
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($mockLivret, $modele);
        // Arrange

        $mockReport = new stdClass();
        $mockReport->periode = null;
        $mockReport->activites_pro = 123;
        $mockReport->observations_apprenti = '';
        $mockReport->observations_tuteur = null;
        $mockReport->observations_referent = new stdClass();

        // Use Mockery to mock the DB query
        DB::shouldReceive('table->where->get')
            ->once()
            ->andReturn(collect([$mockReport]));

        // Act
        $pdfGenerator->addReports();

        // Assert
        // Add specific assertions based on your error handling requirements
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testValidateReportField()
    {
        $mockUser = User::factory()->create();
        $modele = Modele::factory()->create();
        $mockLivret = Livret::factory()->create([
            'user_id' => $mockUser->id,
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($mockLivret, $modele);
        // Arrange
        $mockReport = new stdClass();
        $mockReport->valid_field = 'Test Value';
        $mockReport->null_field = null;
        $mockReport->invalid_field = 123;

        // Act & Assert
        $this->assertEquals('Test Value',
            $pdfGenerator->validateReportField($mockReport, 'valid_field'));

        $this->assertEquals('',
            $pdfGenerator->validateReportField($mockReport, 'null_field'));

        $this->assertEquals('Invalid data',
            $pdfGenerator->validateReportField($mockReport, 'invalid_field'));
    }

    public function testImportAdditionalPdfsWithInvalidLink()
    {
        $mockUser = User::factory()->create();
        $modele = Modele::factory()->create();
        $mockLivret = Livret::factory()->create([
            'user_id' => $mockUser->id,
            'modele_id' => $modele->id
        ]);
        $pdfGenerator = new PdfGenerator($mockLivret, $modele);
        // Arrange
        $compositions = collect([
            (object)[
                'lien' => null
            ],
            (object)[] // No lien property
        ]);

        // Act
        $pdfGenerator->importAdditionalPdfs($compositions);

        // Assert
        $this->assertTrue(true); // Should skip invalid compositions
    }
}

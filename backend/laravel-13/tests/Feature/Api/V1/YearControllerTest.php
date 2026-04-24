<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;

class YearControllerTest extends TestCase
{
    use RefreshDatabase;  // Resets database after each test

    /**
     * Test: Can list all years
     */
    public function test_can_list_years()
    {
        // Arrange: Create 3 years
        Year::factory()->create(['year' => 2023]);
        Year::factory()->create(['year' => 2024]);
        Year::factory()->create(['year' => 2025]);

        // Act: Make request
        $response = $this->getJson('/api/v1/years');

        // Assert: Verify response
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.year', 2025)  // Descending order
            ->assertJsonPath('data.1.year', 2024)
            ->assertJsonPath('data.2.year', 2023);
    }

    /**
     * Test: Can create a year
     */
    public function test_can_create_year()
    {
        // Act
        $response = $this->postJson('/api/v1/years', [
            'year' => 2026
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('data.year', 2026);

        $this->assertDatabaseHas('years', [
            'year' => 2026
        ]);
    }

    /**
     * Test: Cannot create duplicate year
     */
    public function test_cannot_create_duplicate_year()
    {
        // Arrange: Create existing year
        Year::factory()->create(['year' => 2026]);

        // Act
        $response = $this->postJson('/api/v1/years', [
            'year' => 2026
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /**
     * Test: Year must be 4 digits
     */
    public function test_year_must_be_4_digits()
    {
        $response = $this->postJson('/api/v1/years', [
            'year' => 25  // Only 2 digits
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /**
     * Test: Year must be integer
     */
    public function test_year_must_be_integer()
    {
        $response = $this->postJson('/api/v1/years', [
            'year' => 'twenty twenty-five'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /**
     * Test: Can show a single year
     */
    public function test_can_show_single_year()
    {
        // Arrange
        $year = Year::factory()->create(['year' => 2025]);

        // Act
        $response = $this->getJson("/api/v1/years/{$year->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $year->id)
            ->assertJsonPath('data.year', 2025);
    }

    /**
     * Test: Show returns 404 for non-existent year
     */
    public function test_show_returns_404_for_nonexistent_year()
    {
        $response = $this->getJson('/api/v1/years/99999');

        $response->assertStatus(404);
    }

    /**
     * Test: Can update a year
     */
    public function test_can_update_year()
    {
        // Arrange
        $year = Year::factory()->create(['year' => 2025]);

        // Act
        $response = $this->putJson("/api/v1/years/{$year->id}", [
            'year' => 2026
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.year', 2026);

        $this->assertDatabaseHas('years', [
            'id' => $year->id,
            'year' => 2026
        ]);
    }

    /**
     * Test: Update cannot duplicate another year
     */
    public function test_update_cannot_duplicate_another_year()
    {
        // Arrange: Create two years
        $year1 = Year::factory()->create(['year' => 2025]);
        $year2 = Year::factory()->create(['year' => 2026]);

        // Act: Try to update year1 to 2026 (already taken by year2)
        $response = $this->putJson("/api/v1/years/{$year1->id}", [
            'year' => 2026
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /**
     * Test: Can delete a year
     */
    public function test_can_delete_year()
    {
        // Arrange
        $year = Year::factory()->create(['year' => 2025]);

        // Act
        $response = $this->deleteJson("/api/v1/years/{$year->id}");

        // Assert
        $response->assertStatus(204);  // No content
        $this->assertDatabaseMissing('years', ['id' => $year->id]);
    }

    /**
     * Test: Delete returns 404 for non-existent year
     */
    public function test_delete_returns_404_for_nonexistent_year()
    {
        $response = $this->deleteJson('/api/v1/years/99999');

        $response->assertStatus(404);
    }
}
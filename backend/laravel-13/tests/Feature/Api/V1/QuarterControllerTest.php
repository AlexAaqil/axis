<?php

use App\Models\Quarter;
use App\Models\Year;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can list quarters', function () {
    $year = Year::factory()->create(['year' => 2025]);
    Quarter::factory()->create(['year_id' => $year->id]);
    Quarter::factory()->create(['year_id' => $year->id]);
    
    $response = $this->getJson('/api/v1/quarters');
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can create quarter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    
    $response = $this->postJson('/api/v1/quarters', [
        'label' => 'Q1',
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
        'year_id' => $year->id,
    ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.label', 'Q1');
    
    $this->assertDatabaseHas('quarters', [
        'label' => 'Q1',
        'year_id' => $year->id,
    ]);
});

test('cannot create duplicate quarter label for same year', function () {
    $year = Year::factory()->create(['year' => 2025]);
    
    Quarter::factory()->create([
        'label' => 'Q1',
        'year_id' => $year->id,
    ]);
    
    $response = $this->postJson('/api/v1/quarters', [
        'label' => 'Q1',
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
        'year_id' => $year->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['label']);
});

test('year must be 4 digits rule is enforced by year existence', function () {
    $response = $this->postJson('/api/v1/quarters', [
        'label' => 'Q1',
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
        'year_id' => 99999,  // Year doesn't exist
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['year_id']);
});

test('can show single quarter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->create(['year_id' => $year->id]);
    
    $response = $this->getJson("/api/v1/quarters/{$quarter->id}");
    
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $quarter->id);
});

test('show returns 404 for non-existent quarter', function () {
    $response = $this->getJson('/api/v1/quarters/99999');
    $response->assertStatus(404);
});

test('can update quarter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->create([
        'label' => 'Q1',
        'year_id' => $year->id,
    ]);
    
    $response = $this->putJson("/api/v1/quarters/{$quarter->id}", [
        'label' => 'Q2',
        'start_date' => '2025-04-01',
        'end_date' => '2025-06-30',
        'year_id' => $year->id,
    ]);
    
    $response->assertStatus(200)
        ->assertJsonPath('data.label', 'Q2');
    
    $this->assertDatabaseHas('quarters', [
        'id' => $quarter->id,
        'label' => 'Q2',
    ]);
});

test('can delete quarter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->create(['year_id' => $year->id]);
    
    $response = $this->deleteJson("/api/v1/quarters/{$quarter->id}");
    
    $response->assertStatus(204);
    $this->assertDatabaseMissing('quarters', ['id' => $quarter->id]);
});

test('delete returns 404 for non-existent quarter', function () {
    $response = $this->deleteJson('/api/v1/quarters/99999');
    $response->assertStatus(404);
});
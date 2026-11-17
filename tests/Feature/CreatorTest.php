<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_names_and_student_ids_are_visible_in_footer_and_about(): void
    {
        $this->seed();
        $this->get('/tentang')->assertOk()->assertSee('Aviel Aquino')->assertSee('Kent Nathan Herlambang')->assertSee('Pharrel Jibril Tristan')->assertSee('Bangkit Ahktar Fahd')->assertSee('2802393711')->assertSee('2802397464')->assertSee('2802393964')->assertSee('2802401625')->assertSee('images/creators/aviel.png')->assertSee('images/creators/kent.png')->assertSee('images/creators/pharrel.jpg')->assertSee('images/creators/bangkit.png');
    }

    public function test_admin_can_upload_real_creator_photo(): void
    {
        Storage::fake('local');
        $creator = Creator::create(['name' => 'Aviel Aquino', 'student_id' => '2802393711', 'position' => 0]);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.creators.update', $creator), ['photo' => UploadedFile::fake()->image('aviel.jpg', 400, 400)])->assertRedirect()->assertSessionHas('success');
        $creator->refresh();
        Storage::disk('local')->assertExists($creator->photo_path);
        $this->get(route('creators.photo', $creator))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('admin.creators'))->assertOk()->assertSee('Aviel Aquino');
    }

    public function test_customer_cannot_replace_creator_photos(): void
    {
        Storage::fake('local');
        $creator = Creator::create(['name' => 'Aviel Aquino', 'student_id' => '2802393711', 'position' => 0]);
        $this->actingAs(User::factory()->create())->post(route('admin.creators.update', $creator), ['photo' => UploadedFile::fake()->image('wrong.jpg')])->assertForbidden();
        $this->assertDatabaseHas('creators', ['id' => $creator->id, 'photo_path' => null]);
        Storage::disk('local')->assertDirectoryEmpty('creator-photos');
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('local');
        $creator = Creator::create(['name' => 'Aviel Aquino', 'student_id' => '2802393711', 'position' => 0]);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.creators.update', $creator), ['photo' => UploadedFile::fake()->create('script.php', 1, 'text/plain')])->assertSessionHasErrors('photo');
        $this->assertDatabaseHas('creators', ['id' => $creator->id, 'photo_path' => null]);
    }
}

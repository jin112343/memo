<?php
namespace Tests\Feature;
use App\Memo;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemoSecurityTest extends TestCase
{
    use RefreshDatabase;
    private function user(string $email): User
    {
        return User::create(['name' => 'Test', 'email' => $email, 'password' => Hash::make('test-only-password')]);
    }
    private function memo(?User $user, string $content): Memo
    {
        $memo = new Memo(['content' => $content]);
        $memo->user_id = $user?->id;
        $memo->save();
        return $memo;
    }
    public function test_anonymous_visitors_cannot_read_or_change_memos(): void
    {
        $memo = $this->memo(null, 'private legacy memo');
        $this->get('/')->assertUnauthorized();
        $this->post('/store', ['content' => 'unauthorized'])->assertUnauthorized();
        $this->post('/update', ['id' => $memo->id, 'content' => 'changed'])->assertUnauthorized();
        $this->delete('/delete?id='.$memo->id)->assertUnauthorized();
        $this->assertDatabaseHas('memos', ['id' => $memo->id, 'content' => 'private legacy memo']);
    }
    public function test_only_the_current_users_memos_are_visible(): void
    {
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        $this->memo($owner, 'my private memo');
        $this->memo($other, 'someone elses secret');
        $this->memo(null, 'unclaimed legacy secret');
        $this->actingAs($owner)->get('/')->assertOk()->assertSee('my private memo')
            ->assertDontSee('someone elses secret')->assertDontSee('unclaimed legacy secret');
    }
    public function test_cross_user_access_and_mutation_are_rejected(): void
    {
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        $memo = $this->memo($other, 'unchanged');
        $this->actingAs($owner)->get('/edit?id='.$memo->id)->assertNotFound();
        $this->post('/update', ['id' => $memo->id, 'content' => 'changed'])->assertNotFound();
        $this->delete('/delete?id='.$memo->id)->assertNotFound();
        $this->assertDatabaseHas('memos', ['id' => $memo->id, 'content' => 'unchanged']);
    }
    public function test_owner_can_create_edit_and_delete_without_reassigning_ownership(): void
    {
        $owner = $this->user('owner@example.test');
        $other = $this->user('other@example.test');
        $this->actingAs($owner)->post('/store', ['content' => 'mine', 'user_id' => $other->id])->assertRedirect('/');
        $memo = Memo::sole();
        $this->assertSame($owner->id, $memo->user_id);
        $this->post('/update', ['id' => $memo->id, 'content' => 'updated', 'user_id' => $other->id])->assertRedirect('/');
        $this->assertDatabaseHas('memos', ['id' => $memo->id, 'content' => 'updated', 'user_id' => $owner->id]);
        $this->delete('/delete?id='.$memo->id)->assertRedirect('/');
        $this->assertDatabaseMissing('memos', ['id' => $memo->id]);
    }
    public function test_get_cannot_delete_and_content_length_matches_database(): void
    {
        $owner = $this->user('owner@example.test');
        $memo = $this->memo($owner, 'keep');
        $this->actingAs($owner)->get('/delete?id='.$memo->id)->assertStatus(405);
        $this->post('/store', ['content' => str_repeat('a', 301)])->assertSessionHasErrors('content');
        $this->assertDatabaseCount('memos', 1);
    }
    public function test_delete_requires_a_csrf_token_outside_the_test_bypass(): void
    {
        $owner = $this->user('owner@example.test');
        $memo = $this->memo($owner, 'keep');
        $this->actingAs($owner);
        $this->app->instance('env', 'local');
        $this->delete('/delete?id='.$memo->id)->assertStatus(419);
        $this->assertDatabaseHas('memos', ['id' => $memo->id]);
    }
}

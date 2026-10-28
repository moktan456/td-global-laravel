<?php

namespace Tests\Feature;

use App\Filament\Pages\ArticlesPage;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\ButtonsAndForms;
use App\Filament\Pages\FaqPage;
use App\Filament\Pages\HomePage;
use App\Filament\Pages\Menus;
use App\Filament\Pages\PageText;
use App\Filament\Pages\PhotosPage;
use App\Filament\Pages\PricingPage;
use App\Filament\Pages\ProcessPage;
use App\Filament\Pages\ServicesPage;
use App\Filament\Pages\SiteSettings;
use App\Filament\Pages\UserGuide;
use App\Models\ContentOverride;
use App\Models\User;
use App\Support\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private const PAGES = [SiteSettings::class, Menus::class, HomePage::class, PricingPage::class, ServicesPage::class, ArticlesPage::class, FaqPage::class, ProcessPage::class, PhotosPage::class, PageText::class, ButtonsAndForms::class];

    private const PUBLIC = ['/', '/about', '/services', '/services/import-clearance', '/blog', '/blog/invoice-and-packing-list-must-match', '/pricing', '/calculator', '/contact', '/quote', '/clearing-agent-gelephu', '/credits', '/search?q=clearance'];

    private function admin(): User
    {
        return $this->staff(['role' => 'admin']);
    }

    public function test_only_admins_can_open_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs($this->staff(['role' => 'staff']))->get('/admin')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Edit your website');
    }

    public function test_a_switched_off_admin_cannot_open_it(): void
    {
        $this->actingAs($this->staff(['role' => 'admin', 'active' => false]))->get('/admin')->assertStatus(403);
    }

    public function test_an_admin_on_a_temporary_password_is_sent_to_choose_one(): void
    {
        $this->actingAs($this->staff(['role' => 'admin', 'must_change_password' => true]))->get('/admin')->assertRedirect('/staff/password');
    }

    public function test_admin_signs_in_with_the_staff_username(): void
    {
        $this->staff(['role' => 'admin', 'username' => 'tashi']);
        Livewire::test(Login::class)
            ->fillForm(['email' => 'Tashi', 'password' => 'a-long-password-1'])
            ->call('authenticate')
            ->assertHasNoFormErrors();
        $this->assertAuthenticated();
    }

    public function test_staff_who_are_not_admins_cannot_sign_in_to_the_admin(): void
    {
        $this->staff(['role' => 'staff', 'username' => 'dorji']);
        Livewire::test(Login::class)
            ->fillForm(['email' => 'dorji', 'password' => 'a-long-password-1'])
            ->call('authenticate');
        $this->assertGuest();
    }

    public function test_every_page_opens(): void
    {
        $this->actingAs($this->admin());
        foreach (self::PAGES as $page) {
            Livewire::test($page)->assertOk();
        }
    }

    public function test_saving_every_page_unchanged_leaves_the_website_exactly_as_it_was(): void
    {
        $before = [];
        foreach (self::PUBLIC as $path) {
            $before[$path] = $this->get($path)->assertOk()->getContent();
        }

        $this->actingAs($this->admin());
        foreach (self::PAGES as $page) {
            Livewire::test($page)->call('save')->assertHasNoFormErrors();
        }

        foreach (self::PUBLIC as $path) {
            $after = $this->get($path)->assertOk()->getContent();
            $this->assertSame(preg_replace('/csrf-token" content="[^"]*"/', '', $before[$path]), preg_replace('/csrf-token" content="[^"]*"/', '', $after), "$path changed after saving without edits");
        }
    }

    public function test_an_edit_shows_on_the_website_and_can_be_undone(): void
    {
        $this->actingAs($this->admin());
        $this->get('/')->assertDontSee('Brand new tagline');

        Livewire::test(SiteSettings::class)
            ->fillForm(['site.site.tagline' => 'Brand new tagline'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->get('/')->assertSee('Brand new tagline');

        Livewire::test(SiteSettings::class)->callAction('reset');
        $this->get('/')->assertDontSee('Brand new tagline');
        $this->assertDatabaseMissing('content_overrides', ['name' => 'site']);
    }

    public function test_services_can_be_removed(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(ServicesPage::class);
        $data = $page->get('data');
        $removed = array_pop($data['services']['list']);
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();

        $this->assertCount(count(Content::defaults('services')) - 1, Content::load('services'));
        $this->get('/services/'.$removed['slug'])->assertNotFound();
    }

    public function test_the_home_banner_and_headings_come_from_the_admin(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(HomePage::class);
        $data = $page->get('data');
        $first = array_key_first($data['home']['slides']);
        $data['home']['slides'][$first]['title'] = 'A banner headline from the admin';
        $data['home']['sections']['why']['title'] = 'A why heading from the admin';
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertSee('A banner headline from the admin')->assertSee('A why heading from the admin');
    }

    public function test_a_new_article_appears_on_the_blog(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(ArticlesPage::class);
        $data = $page->get('data');
        $data['articles']['list'][] = ['title' => 'A fresh article', 'slug' => 'a-fresh-article', 'category' => 'News', 'excerpt' => 'Short.', 'date' => '2026-10-01', 'author' => 'Tashi', 'image' => 'svcDocs', 'body' => [['heading' => 'Hello', 'paragraphs' => [['text' => 'First paragraph.']], 'list' => []]]];
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();
        $this->get('/blog/a-fresh-article')->assertOk()->assertSee('A fresh article')->assertSee('First paragraph.');
        $this->get('/blog')->assertSee('A fresh article');
    }

    public function test_content_reads_the_files_when_there_are_no_edits(): void
    {
        $this->assertSame(Content::defaults('faq'), Content::load('faq'));
        ContentOverride::create(['name' => 'faq', 'data' => []]);
        Content::flush();
        $this->assertSame([], Content::load('faq'), 'a list emptied in the admin stays empty');
    }

    public function test_uploaded_photos_are_served_and_other_paths_are_not(): void
    {
        \Storage::fake('public');
        \Storage::disk('public')->put('uploads/x.png', 'png');
        $this->get('/media/uploads/x.png')->assertOk();
        $this->get('/media/uploads/missing.png')->assertNotFound();
        $this->get('/media/uploads/x.php')->assertNotFound();
        $this->get('/media/..%2F.env')->assertNotFound();
    }

    public function test_the_staff_menu_links_to_the_admin_only_for_admins(): void
    {
        $this->actingAs($this->admin())->get('/staff')->assertSee('Edit website content');
        $this->actingAs($this->staff(['role' => 'staff']))->get('/staff')->assertDontSee('Edit website content');
    }

    public function test_an_uploaded_photo_replaces_a_library_photo(): void
    {
        \Storage::fake('public');
        $this->actingAs($this->admin());
        $page = Livewire::test(PhotosPage::class);
        $data = $page->get('data');
        $row = array_key_first($data['images']['list']);
        $key = $data['images']['list'][$row]['key'];
        $page->set("data.images.list.$row.upload", [UploadedFile::fake()->image('mine.jpg', 800, 600)])->call('save')->assertHasNoFormErrors();

        $this->assertStringStartsWith('/media/uploads/', image_src($key));
        \Storage::disk('public')->assertExists(substr(image_src($key), strlen('/media/')));
        $this->get('/')->assertOk()->assertSee(image_src($key), false);
    }

    public function test_page_wording_and_its_tokens_come_from_the_admin(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(PageText::class);
        $data = $page->get('data');
        $data['pages']['about']['title'] = 'Who we are';
        $data['pages']['about']['metaDescription'] = 'We are {name}, UEN {uen}.';
        $data['pages']['service']['ctaTitle'] = 'Ready for {service}?';
        $data['pages']['footer']['linksHeading'] = 'Quick links';
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();

        $this->get('/about')->assertSee('Who we are')->assertSee('We are TD Global Clearing &amp; Logistics Pte. Ltd., UEN A7649744Y.', false);
        $this->get('/services/import-clearance')->assertSee('Ready for import clearance?');
        $this->get('/')->assertSee('Quick links');
    }

    public function test_button_form_and_message_text_come_from_the_admin(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(ButtonsAndForms::class);
        $data = $page->get('data');
        $data['labels']['quoteForm']['submit'] = 'Send my request';
        $data['labels']['contactForm']['nameLabel'] = 'Your full name';
        $data['labels']['buttons']['staffLogin'] = 'Team sign-in';
        $data['labels']['messages']['enterName'] = 'Please tell us your name.';
        $data['labels']['messages']['quoteSuccess'] = 'Cheers {name}, quote on its way to {email}.';
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();

        config(['mail.default' => 'smtp']);
        Mail::fake();
        $this->get('/quote')->assertSee('Send my request');
        $this->get('/contact')->assertSee('Your full name');
        $this->get('/')->assertSee('Team sign-in');
        $this->post('/contact', ['name' => '', 'email' => 'a@example.com', 'message' => 'hi'], ['X-Enhance' => '1'])->assertSee('Please tell us your name.');
        $this->post('/quote', ['name' => 'Tashi', 'email' => 'tashi@example.com', 'direction' => 'import', 'mode' => 'road', 'goods' => 'x'], ['X-Enhance' => '1'])
            ->assertSee('Cheers Tashi, quote on its way to tashi@example.com.');
    }

    public function test_credits_hidden_text_and_other_wording_come_from_the_admin(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(PageText::class);
        $data = $page->get('data');
        $data['pages']['credits']['title'] = 'Image credits';
        $data['pages']['footer']['copyright'] = 'Copyright {year} {legalName}.';
        $data['pages']['notFound']['title'] = 'Nothing here';
        $data['pages']['search']['manyResults'] = 'Found {count} matches for {query}';
        $row = array_key_first($data['credits']['list']);
        $data['credits']['list'][$row]['photographer'] = 'Pema Dorji';
        $page->set('data', $data)->call('save')->assertHasNoFormErrors();

        $labels = Livewire::test(ButtonsAndForms::class);
        $l = $labels->get('data');
        $l['labels']['a11y']['skip'] = 'Jump to the content';
        $l['labels']['a11y']['searchPlaceholder'] = 'Find…';
        $l['labels']['calculatorForm']['baseFee'] = 'Starting fee';
        $labels->set('data', $l)->call('save')->assertHasNoFormErrors();

        $this->get('/credits')->assertSee('Image credits')->assertSee('Pema Dorji');
        $this->get('/')->assertSee('Copyright '.date('Y').' TD Global Clearing &amp; Logistics Pte. Ltd.', false)->assertSee('Jump to the content')->assertSee('placeholder="Find…"', false);
        $this->get('/no-such-page')->assertNotFound()->assertSee('Nothing here');
        $this->get('/search?q=clearance')->assertSee('matches for clearance');
        $this->get('/calculator')->assertSee('Starting fee');
    }

    public function test_the_user_guide_is_in_the_admin_for_admins_only(): void
    {
        $this->get('/admin/guide')->assertRedirect('/admin/login');
        $this->actingAs($this->staff(['role' => 'staff']))->get('/admin/guide')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/guide')->assertOk()->assertSee('Website and tracking guide')->assertSee('Write a new blog article')->assertSee('Create a request and a tracking number');
        Livewire::test(UserGuide::class)->assertOk();
    }
}

{{-- The website-editing guide as plain HTML. Shown in the admin (App\Filament\Pages\UserGuide) and published as a shareable page. Plain HTML only: no Blade code, so the same text can be reused. --}}
<nav class="guide-toc" aria-label="In this guide">
    <p class="guide-toc-title">In this guide</p>
    <ol>
        <li><a href="#signin">Signing in</a></li>
        <li><a href="#basics">How every page works</a></li>
        <li><a href="#where">Where to change what</a></li>
        <li><a href="#tasks">Common jobs, step by step</a></li>
        <li><a href="#writing">Writing tips</a></li>
        <li><a href="#help">If something goes wrong</a></li>
        <li><a href="#limits">What you cannot change here</a></li>
    </ol>
</nav>

<section id="signin">
    <h2>Signing in</h2>
    <p>Open <strong>/admin</strong> on the website, for example <code>https://yourdomain/admin</code>. Sign in with the same username and password you use for the staff area.</p>
    <ul>
        <li>Only <strong>super admins</strong> can sign in here. Other staff see "forbidden". A super admin can give someone this role in the staff area, under <em>Team</em>.</li>
        <li>If you were given a temporary password, you are first sent to choose your own. Do that, then open <strong>/admin</strong> again.</li>
        <li>You are signed out after about two hours without activity. Sign in again and carry on.</li>
        <li>The menu in the top right has <strong>View website</strong>, <strong>Staff area</strong> and <strong>Sign out</strong>. From the staff area, super admins also have <strong>Edit website content</strong> in their account menu.</li>
    </ul>
</section>

<section id="basics">
    <h2>How every page works</h2>
    <p>The left menu lists everything you can edit. Each page shows the text the way it appears on the website. The routine is always the same:</p>
    <ol class="steps">
        <li><strong>Open a page</strong> from the left menu, or from the cards on <em>Start here</em>.</li>
        <li><strong>Change the text.</strong> Some pages have tabs across the top. Click a tab to see another group of fields.</li>
        <li>Press <strong>Save changes</strong> (or <kbd>Ctrl</kbd>/<kbd>⌘</kbd> + <kbd>S</kbd>). A green message confirms it.</li>
        <li>Press <strong>View page</strong> at the top to open the website page in a new tab and check it.</li>
    </ol>
    <p>The website changes the moment you save. There is no separate "publish" step and nothing to wait for.</p>

    <h3>Buttons at the top of a page</h3>
    <dl>
        <dt>View page</dt>
        <dd>Opens the matching page of the website in a new tab.</dd>
        <dt>Back to the original text</dt>
        <dd>Removes every change you made <em>on that page</em> and brings back the text the website came with. You are asked to confirm. This cannot be undone.</dd>
    </dl>

    <h3>Lists (services, articles, questions, steps, menu links)</h3>
    <ul>
        <li>Click a row's arrow to open or close it. <strong>Collapse all</strong> and <strong>Expand all</strong> sit above the list.</li>
        <li><strong>Drag the handle</strong> (the arrows at the left of a row) to change the order.</li>
        <li>The copy icon duplicates a row. The bin icon deletes it. Nothing is deleted from the website until you press <strong>Save changes</strong>.</li>
        <li>The <strong>Add …</strong> button at the bottom of a list adds a new row.</li>
    </ul>

    <h3>Special text</h3>
    <ul>
        <li><strong>Words in [square brackets]</strong> are placeholders, such as <code>[Team member name]</code>. The website hides them. Replace them with real text to show that item.</li>
        <li><strong>Words in {curly brackets}</strong>, such as <code>{name}</code> or <code>{phone}</code>, are filled in by the website. Each place that allows them says which ones. Leave them as they are to keep the text up to date when you change the company details.</li>
        <li>A red <strong>required</strong> mark means the field cannot be empty. If a red message appears when you save, scroll to the field it points at.</li>
    </ul>
</section>

<section id="where">
    <h2>Where to change what</h2>
    <p>Find what you want to change on the website, then open the page in the middle column.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>What you want to change</th><th>Open</th><th>Look in</th></tr>
            </thead>
            <tbody>
                <tr><td>Phone, email, address, opening hours, WhatsApp number, map</td><td>Company &amp; contact</td><td>Contact tab</td></tr>
                <tr><td>Company name, tagline, registration number, social media links</td><td>Company &amp; contact</td><td>Company and Search &amp; social tabs</td></tr>
                <tr><td>Top menu and footer links</td><td>Menus &amp; footer links</td><td></td></tr>
                <tr><td>Home page banner (headline, text, button, photos)</td><td>Home page</td><td>Banner tab</td></tr>
                <tr><td>Home page section headings</td><td>Home page</td><td>Section headings tab</td></tr>
                <tr><td>Why choose us, document tips, trust badges, partners</td><td>Home page</td><td>Why choose us, Partners &amp; badges tabs</td></tr>
                <tr><td>Team members and testimonials</td><td>Home page</td><td>Team, Testimonials tabs</td></tr>
                <tr><td>Heading and intro of the About, Services, Pricing, Calculator, Contact, Quote and Blog pages</td><td>Page text</td><td>One tab per page</td></tr>
                <tr><td>Registration certificates section on About</td><td>Page text</td><td>About tab</td></tr>
                <tr><td>Footer sentence, copyright line</td><td>Page text</td><td>Footer tab</td></tr>
                <tr><td>Photo credits page, "page not found" page, search results wording</td><td>Page text</td><td>Photo credits, Page not found &amp; search tabs</td></tr>
                <tr><td>Price packs</td><td>Pricing &amp; calculator</td><td></td></tr>
                <tr><td>Calculator fees and discount</td><td>Pricing &amp; calculator</td><td>Cost calculator box</td></tr>
                <tr><td>Each service and its page</td><td>Services</td><td></td></tr>
                <tr><td>Blog articles</td><td>Blog articles</td><td></td></tr>
                <tr><td>Questions and answers on the Gelephu page</td><td>Questions &amp; answers</td><td></td></tr>
                <tr><td>The four "how it works" steps</td><td>How it works (steps)</td><td></td></tr>
                <tr><td>Button text such as "Request a free quote" or "Learn more"</td><td>Buttons &amp; forms</td><td>Buttons &amp; links tab</td></tr>
                <tr><td>Labels and messages on the contact form, quote form and calculator</td><td>Buttons &amp; forms</td><td>Contact form, Quote form, Calculator, Messages tabs</td></tr>
                <tr><td>Text read by screen readers, the search box hint</td><td>Buttons &amp; forms</td><td>Hidden text tab</td></tr>
                <tr><td>Any photo on the website</td><td>Photos</td><td></td></tr>
            </tbody>
        </table>
    </div>
</section>

<section id="tasks">
    <h2>Common jobs, step by step</h2>

    <article>
        <h3>Change the phone number or email</h3>
        <ol class="steps">
            <li>Open <strong>Company &amp; contact</strong> and go to the <strong>Contact</strong> tab.</li>
            <li>Change <em>Phone (as shown)</em>, for example <code>+975 17975757</code>.</li>
            <li>Change <em>Phone (for tapping)</em> to the same number with no spaces: <code>+97517975757</code>. This is what a phone dials when a visitor taps the number.</li>
            <li>Change <em>WhatsApp number</em> if needed: digits only, with the country code, like <code>97517975757</code>. Empty it to hide WhatsApp everywhere.</li>
            <li>Press <strong>Save changes</strong>. The top bar, footer, Contact page, Quote page and the floating buttons all update.</li>
        </ol>
    </article>

    <article>
        <h3>Write a new blog article</h3>
        <ol class="steps">
            <li>Open <strong>Blog articles</strong> and press <strong>Write a new article</strong> at the bottom.</li>
            <li>Fill in the <em>Title</em>, <em>Category</em>, <em>Date</em>, <em>Author</em> and a <em>Cover photo</em>. Write a short <em>summary</em>: it appears on the blog list and in search results.</li>
            <li>Choose the <em>Web address ending</em>, such as <code>how-to-read-an-hs-code</code>. Use lower-case letters, numbers and dashes. The article will live at <code>/blog/how-to-read-an-hs-code</code>.</li>
            <li>Build the article from <strong>sections</strong>. A section can have a heading, one or more paragraphs and a bullet list. Press <em>Add a section</em> for each new heading.</li>
            <li>Press <strong>Save changes</strong>, then <strong>View page</strong> and open the article from the blog list.</li>
        </ol>
        <p class="note">The newest date shows first on the blog. To hide an article without deleting it, there is no switch yet: delete the row, or move its date far into the past.</p>
    </article>

    <article>
        <h3>Edit or add a service</h3>
        <ol class="steps">
            <li>Open <strong>Services</strong> and click the service to open it.</li>
            <li><em>The page</em> holds the name, one-line summary, photo and icon. <em>Main text</em> holds the paragraphs and the "what is included" list. <em>Common problems</em> and <em>Google search listing</em> are folded away until you need them.</li>
            <li>To add a service, press <strong>Add a service</strong> at the bottom and fill in every required field.</li>
            <li>Save. The service appears on the Services page, on the home page and on its own page at <code>/services/its-web-address</code>.</li>
        </ol>
        <p class="warn">Do not change the <em>Web address ending</em> of a service that is already live. Anyone with the old link, and Google, would land on a "page not found".</p>
    </article>

    <article>
        <h3>Replace a photo</h3>
        <ol class="steps">
            <li>Open <strong>Photos</strong>. Each row is a place on the website where a photo appears, such as "Home banner: photo 1" or "Import clearance".</li>
            <li>Open the row. Under <em>Your own photo</em>, drop in a JPG, PNG or WebP file, up to 8 MB. It replaces the photo that came with the website.</li>
            <li>In <em>Describe the photo</em>, say what is in the picture, for example "A forklift unloading pallets from a container". Screen readers read this aloud and it shows if the photo cannot load.</li>
            <li>Save. To go back to the original photo, remove your upload and save again.</li>
        </ol>
        <p class="note">Use a landscape photo at least 1600 pixels wide. The website crops photos to fit each space, so keep the subject near the middle. Only use photos you own or have permission to use.</p>
    </article>

    <article>
        <h3>Change the price packs or the calculator</h3>
        <ol class="steps">
            <li>Open <strong>Pricing &amp; calculator</strong>.</li>
            <li>In <em>Price packs</em>, edit the name, the price as customers should read it (for example <code>Nu. 2,000</code>) and the lines it includes. Turn on <em>Show as "most popular"</em> for one pack.</li>
            <li>In <em>Cost calculator</em>, enter the numbers as plain figures. <em>Export discount</em> is a fraction: <code>0.1</code> means 10% off.</li>
            <li>Save, then try the calculator on the website. It is an estimate only, and the page says so.</li>
        </ol>
    </article>

    <article>
        <h3>Change the home page banner</h3>
        <ol class="steps">
            <li>Open <strong>Home page</strong>, <strong>Banner</strong> tab. Each slide has a small label, a headline, a sentence of text and a button.</li>
            <li>Choose the three photos for each slide from the list. To use your own pictures, add them first under <strong>Photos</strong>.</li>
            <li>You can have between one and five slides. Drag to reorder.</li>
        </ol>
    </article>

    <article>
        <h3>Add a question to the FAQ</h3>
        <ol class="steps">
            <li>Open <strong>Questions &amp; answers</strong> and press <strong>Add a question</strong>.</li>
            <li>Write the question as a visitor would ask it, then a short, direct answer.</li>
            <li>Save. The question appears on the "Clearing agent in Gelephu" page and is also given to Google as a rich answer.</li>
        </ol>
    </article>

    <article>
        <h3>Change a menu or footer link</h3>
        <ol class="steps">
            <li>Open <strong>Menus &amp; footer links</strong>.</li>
            <li>Each link has the <em>text shown</em> and where it <em>links to</em>. For a page on this website type a path such as <code>/services</code>. For another site type the full address, starting with <code>https://</code>.</li>
            <li>Add, remove or drag links to reorder. Keep the top menu short: five or six links fit best.</li>
        </ol>
    </article>

    <article>
        <h3>Change a button or form label</h3>
        <ol class="steps">
            <li>Open <strong>Buttons &amp; forms</strong>.</li>
            <li>Pick the tab: <em>Buttons &amp; links</em> for text like "Learn more", <em>Contact form</em>, <em>Quote form</em> or <em>Calculator</em> for field labels, <em>Messages</em> for what visitors read after sending.</li>
            <li>In <em>Messages</em>, <code>{name}</code> and <code>{email}</code> insert what the visitor typed.</li>
        </ol>
    </article>

    <article>
        <h3>Show the team or testimonials</h3>
        <ol class="steps">
            <li>Open <strong>Home page</strong>, then the <strong>Team</strong> or <strong>Testimonials</strong> tab.</li>
            <li>Replace the <code>[bracketed]</code> placeholders with real names, roles, quotes and companies. The team section stays hidden until a member has a real name, and testimonials stay hidden until one has both a real quote and a real name.</li>
            <li>Only publish a testimonial with the client's permission.</li>
        </ol>
    </article>
</section>

<section id="writing">
    <h2>Writing tips</h2>
    <ul>
        <li><strong>Search listing titles</strong> work best under about 60 characters, and <strong>descriptions</strong> under about 160. They are what people see in Google before they click.</li>
        <li><strong>Describe every photo</strong> in plain words. It helps people who cannot see it, and search engines.</li>
        <li><strong>Keep a link's text meaningful.</strong> "Request a free quote" is better than "Click here".</li>
        <li><strong>Be consistent.</strong> If you write "Nu. 2,000" in one place, write prices the same way everywhere.</li>
        <li><strong>Check on a phone.</strong> Most visitors use one. Open the website on your phone after a big change.</li>
        <li><strong>Make one change at a time</strong> on a page you do not know well, so it is easy to see what caused a problem.</li>
    </ul>
</section>

<section id="help">
    <h2>If something goes wrong</h2>
    <dl>
        <dt>"Forbidden" or I cannot sign in</dt>
        <dd>Your account is not a super admin, or it has been switched off. Ask a super admin to check it under <em>Team</em> in the staff area. Wrong password: the sign-in page does not say which part was wrong, on purpose.</dd>
        <dt>I saved but the website looks the same</dt>
        <dd>Reload the website page. If it still looks the same, check that you saved on the right page: the table above shows which page holds each text. Some text is inside a tab you did not open.</dd>
        <dt>A red message stops me saving</dt>
        <dd>A required field is empty, or a value is not in the right form (a web address must start with <code>https://</code>, an email needs an <code>@</code>). Scroll to the field the message names.</dd>
        <dt>My photo will not upload</dt>
        <dd>The file must be a JPG, PNG or WebP under 8 MB. Make it smaller and try again.</dd>
        <dt>I made a mess of a page</dt>
        <dd>Press <strong>Back to the original text</strong> at the top of that page. It removes your changes on that page only.</dd>
        <dt>A line shows something like <code>{name}</code> on the website</dt>
        <dd>The curly word was spelled differently from the allowed ones. Each field that accepts them lists them. Fix the spelling, or remove the brackets and type the real text.</dd>
        <dt>I deleted something by mistake</dt>
        <dd>If you have not pressed <strong>Save changes</strong> yet, reload the page and it is back. After saving, re-add it by hand, or use <strong>Back to the original text</strong> to return the whole page to how the website came.</dd>
    </dl>
</section>

<section id="limits">
    <h2>What you cannot change here</h2>
    <ul>
        <li><strong>Shipment tracking</strong>, clients, notifications and the team. These are in the <a href="/staff">staff area</a>.</li>
        <li><strong>Colours, fonts and page layout.</strong> These are part of the website's design. Ask your developer.</li>
        <li><strong>The tracking page's wording</strong>, staff screens and the emails sent to clients.</li>
        <li><strong>Accessibility labels</strong> such as "Main menu" and "Breadcrumb". They are technical and should stay as they are.</li>
        <li><strong>Settings such as the email service, Telegram bot and domain.</strong> These are set on the server.</li>
    </ul>
    <p class="note">Your edits are stored in the website's database, separate from its code. A new version of the website never overwrites them.</p>
</section>

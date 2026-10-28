{{-- The website-editing guide as plain HTML. Shown in the admin (App\Filament\Pages\UserGuide) and published as a shareable page. Plain HTML only: no Blade code, so the same text can be reused. --}}
<nav class="guide-toc" aria-label="In this guide">
    <p class="guide-toc-title">In this guide</p>
    <ol>
        <li><a href="#signin">Signing in</a></li>
        <li><a href="#basics">How every page works</a></li>
        <li><a href="#where">Where to change what</a></li>
        <li><a href="#tasks">Common jobs, step by step</a></li>
        <li><a href="#tracking">The tracking system</a></li>
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

<section id="tracking">
    <h2>The tracking system</h2>
    <p>Tracking has two sides. <strong>Staff</strong> create requests and update their stage in the staff area at <strong>/staff</strong>. <strong>Clients</strong> follow progress on the public <strong>Track</strong> page at <strong>/track</strong>, with no account and no sign-in. Everything on this tab is done in the staff area, not in this admin. Any staff member can do the jobs below. Only the <em>Team</em> and <em>Telegram</em> pages need a super admin.</p>

    <h3>How a shipment travels through the system</h3>
    <ol class="steps">
        <li>Staff <strong>register the client</strong> once (name, email, phone).</li>
        <li>Staff <strong>create a request</strong> for that client. The system makes a <strong>tracking number</strong> and sends it to the client by email, and by Telegram if the client is connected.</li>
        <li>As customs and transport move forward, staff <strong>update the stage</strong> and can tick a box to tell the client.</li>
        <li>The client opens the Track page, types the number and sees the stages, the dates and any notes you chose to show.</li>
    </ol>

    <h3>Tracking numbers</h3>
    <p>A number looks like <code>TD-2610-K7QX4M</code>: <code>TD</code>, then the year and month (26 = 2026, 10 = October, Bhutan time), then six letters and digits. Letters that look like digits (O, I, L, 0, 1) are never used, so numbers are easy to read out over the phone. Clients can type it in small letters or without dashes and it still works. Older numbers with four characters at the end keep working too.</p>

    <h3>Find your way around the staff area</h3>
    <ul>
        <li>The sidebar has <strong>Shipments</strong>, <strong>New request</strong> and <strong>Clients</strong>. Super admins also see <strong>Team</strong> and <strong>Telegram</strong>.</li>
        <li><strong>Find a shipment from anywhere:</strong> press <kbd>/</kbd>, type a tracking number or just its six-character code, and press Enter.</li>
        <li>Your name at the bottom of the sidebar opens a menu with <em>Change password</em>, <em>Dark mode</em>, <em>Edit website content</em> (super admins), <em>View website</em> and <em>Sign out</em>.</li>
        <li>On a phone the sidebar becomes a menu bar, and the filters fold away until you open them.</li>
    </ul>

    <article>
        <h3>Register a client</h3>
        <ol class="steps">
            <li>Open <strong>Clients</strong>. Fill in <em>Client's name</em>, <em>Company</em> (optional), <em>Email</em> and <em>Phone</em> (for example <code>17975757</code>).</li>
            <li>Press <strong>Register client</strong>.</li>
            <li>Open the client to see their requests, to turn <em>Email updates</em> and <em>Telegram updates</em> on or off, and to connect Telegram.</li>
        </ol>
        <p class="note">The email and phone you enter are also how a client finds a lost tracking number, so check them carefully.</p>
    </article>

    <article>
        <h3>Create a request and a tracking number</h3>
        <ol class="steps">
            <li>Press <strong>New request</strong> in the sidebar, or from a client's page.</li>
            <li>Choose the <em>Client</em>, <em>Import or export</em> and <em>Shipping method</em>, and describe what is being shipped, for example "2 pallets of auto parts".</li>
            <li>Under what to track, choose <strong>Customs clearance</strong>, <strong>Transport</strong> or both. At least one must stay on.</li>
            <li>If you choose Transport, the <em>Transport details</em> are optional now and you can add them later.</li>
            <li>Choose <em>Looked after by</em> to say who in the team handles it, and add any notes.</li>
            <li>Press <strong>Create request and tracking number</strong>. The number goes to the client straight away. The <strong>Activity</strong> box on the shipment shows what was delivered.</li>
        </ol>
    </article>

    <article>
        <h3>Update a customs stage</h3>
        <ol class="steps">
            <li>Open the shipment from the <strong>Shipments</strong> list, or use the quick <em>Change stage</em> button on the list.</li>
            <li>Under <strong>Update stage</strong>, choose the <em>Stage</em>.</li>
            <li>Optionally write a <em>Note for the client</em>. Anyone who has the tracking number can read it, so never put personal or financial details in it.</li>
            <li>Leave <em>Notify the client about this update</em> ticked to send an email, and a Telegram message if the client is connected. Untick it for an internal correction.</li>
            <li>Press <strong>Save update</strong>. The <strong>Timeline</strong> records each stage with its date and time (Bhutan time) and your note.</li>
        </ol>
        <p>The customs stages, in order:</p>
        <ol class="steps">
            <li>Declaration Submitted</li>
            <li>Pending Valuation</li>
            <li>Pending Risk</li>
            <li>Pending Physical Examination</li>
            <li>Pending Payment</li>
            <li>Pending Release</li>
            <li>Released</li>
        </ol>
    </article>

    <article>
        <h3>Track the transport as well</h3>
        <p>A shipment can follow a second, optional track for the journey itself. Turn it on or off under <strong>What we track</strong> on the shipment, then press Save. Nothing changes until you save.</p>
        <ol class="steps">
            <li>Under <strong>Transport details</strong>, enter the carrier, vehicle number, pickup place, destination and expected delivery date. The driver's name and phone are for staff only and are never shown to the client.</li>
            <li>Move the <em>Transport stage</em> forward as the goods travel: Order Placed, Processing, Ready for Pickup, In Transit, Out for Delivery, Delivered.</li>
            <li>At <strong>Delivered</strong>, fill in the <em>proof of delivery</em>: who received the goods (<em>Received by</em>) and when (<em>Delivered on</em>, Bhutan time; today and now if you leave it).</li>
        </ol>
    </article>

    <article>
        <h3>A client lost their tracking number</h3>
        <ul>
            <li><strong>From the shipment:</strong> open <em>Client lost the tracking number?</em> You can <strong>send it again</strong>, copy the number or the link to the Track page, or send it on WhatsApp.</li>
            <li><strong>By themselves:</strong> on the Track page the client chooses <em>Lost your tracking number?</em> and enters the phone or email they gave you. The numbers are sent to the email, and Telegram, already on file. The page never says whether anything matched, so nobody can use it to find out who your clients are.</li>
        </ul>
    </article>

    <article>
        <h3>Connect a client to Telegram</h3>
        <ol class="steps">
            <li>Open the client and create a <strong>connect link</strong>. It works once.</li>
            <li>Give it to the client: copy it, send it on WhatsApp, or let them scan the <strong>QR code</strong> on your screen.</li>
            <li>The client opens it and presses <strong>Start</strong> in Telegram. From then on they get every update there as well as by email.</li>
            <li>For a new phone or a new Telegram account, create a new link. The old link stops working.</li>
        </ol>
        <p>Clients can also message the bot: <code>/status</code> shows their latest shipments and <code>/stop</code> disconnects Telegram. After <code>/stop</code> they need a new connect link to start again.</p>
    </article>

    <h3>Check that messages arrived</h3>
    <p>Every message has a line in the shipment's <strong>Activity</strong> box, marked <strong>sent</strong>, <strong>failed</strong> or <strong>skipped</strong>, and nothing is hidden. <em>Skipped</em> comes with a reason, such as the client turned email or Telegram off, or Telegram is not connected yet. If a client says they got nothing, look here first, then use <em>Send tracking number again</em>.</p>
    <p class="warn">Email to clients needs the website's email service set up with the company's own domain. Until that is done, messages can fail even though everything else works. Activity shows <em>failed</em> when it happens. Telegram does not depend on it.</p>

    <h3>For super admins</h3>
    <ul>
        <li><strong>Team:</strong> add a staff member with a name, username, temporary password and role (<em>Staff</em> or <em>Super admin</em>). They must choose their own password at first sign-in. You can reset a password or switch someone off. At least one super admin always remains.</li>
        <li><strong>Telegram:</strong> shows a checklist of what is set up. Press <strong>Connect the bot</strong> once, and again if the website address changes. The bot's secret keys are kept on the server and are never shown here.</li>
        <li><strong>Looked after by:</strong> any staff member can assign a shipment to a team member, so everyone knows who is handling it. Filter the list by "Looked after by you".</li>
        <li><strong>Export CSV:</strong> downloads the shipments or the clients list as a spreadsheet.</li>
    </ul>

    <h3>What clients can and cannot see</h3>
    <p>On the Track page, anyone with a tracking number sees the stages, the dates and the notes you chose to write, plus the transport progress if you track it. They never see the client's name, email, phone, the driver's details or your other shipments. The page limits how often one visitor can look up numbers, to stop guessing.</p>
</section>

<section id="limits">
    <h2>What you cannot change here</h2>
    <ul>
        <li><strong>Shipments, clients, notifications and the team.</strong> These are managed in the <a href="/staff">staff area</a>. The tracking section above explains how.</li>
        <li><strong>Colours, fonts and page layout.</strong> These are part of the website's design. Ask your developer.</li>
        <li><strong>The wording of the Track page</strong>, the staff screens, the clearance and transport stage names, and the emails and Telegram messages sent to clients.</li>
        <li><strong>Accessibility labels</strong> such as "Main menu" and "Breadcrumb". They are technical and should stay as they are.</li>
        <li><strong>Settings such as the email service, Telegram bot and domain.</strong> These are set on the server.</li>
    </ul>
    <p class="note">Your edits are stored in the website's database, separate from its code. A new version of the website never overwrites them.</p>
</section>

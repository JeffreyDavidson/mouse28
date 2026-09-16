<x-layouts.app
    title="Privacy information | Mouse28"
    description="How Mouse28 uses contact messages, newsletter details, and the services that help run the site."
    :canonical="route('privacy')"
    :dispatch-layout="true"
>
    <article class="dispatch-paper text-navy mx-auto my-6 max-w-3xl px-6 py-10 sm:my-10 sm:px-10">
        <h1 class="font-heading text-4xl font-semibold tracking-tight sm:text-5xl">Privacy information</h1>
        <p class="mt-6 text-lg/8">
            Mouse28 is run by Jeffrey and Cassie Davidson. Here is how information is used when you read, contact us, or
            subscribe.
        </p>

        <div class="prose prose-navy mt-10 max-w-none">
            <h2>When you contact us</h2>
            <p>
                We store the name, email address, topic, and message you submit so we can read and respond to your
                question. We also send a notification to our inbox and a confirmation to your email address.
            </p>
            <p>
                Contact messages stay in our private admin inbox until we manually delete them. Please avoid including
                medical records, passwords, payment details, or other information that is not needed for your question.
            </p>

            <h2>When you subscribe</h2>
            <p>
                Your email address is sent to Resend, our email and newsletter provider, to manage your subscription and
                send Mouse28 updates. You can unsubscribe using the link in a newsletter or contact us for help.
            </p>

            <h2>Services used by the site</h2>
            <p>
                Hosting is managed through Laravel Forge. Cloudflare Turnstile helps protect our forms from automated
                submissions. These services may process technical request information, such as your IP address and
                browser details, to operate and protect the site.
            </p>
            <p>
                Podcast players are provided by Transistor. Loading or using an embedded player connects your browser to
                that service. Links to other podcast platforms take you to services with their own privacy practices.
            </p>
            <p>
                Sentry and Laravel Nightwatch help us investigate errors and performance problems. Our configuration
                limits personal-data collection and disables request-payload capture in Nightwatch. Technical logs and
                error reports may still contain request metadata.
            </p>

            <h2>Cookies and security</h2>
            <p>
                The site uses session and security cookies for features such as form feedback, request protection, and
                administrator sign-in. Our service providers may also use cookies or similar technology as part of their
                services.
            </p>

            <h2>Questions or deletion requests</h2>
            <p>
                Use our <a href="{{ route('contact.show') }}">contact page</a> or the email address listed there to ask
                about your information, unsubscribe, or request deletion. Tell us which email address or message the
                request concerns, without sending additional sensitive information.
            </p>
            <p>
                Deleting a message from the active inbox does not necessarily remove existing email copies or backup
                copies immediately. We can explain what remains when handling your request.
            </p>
        </div>
    </article>
</x-layouts.app>

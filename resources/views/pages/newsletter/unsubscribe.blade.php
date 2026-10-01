<x-newsletter-action-page
    title="Unsubscribe from Mouse28 updates"
    description="Stop Mouse28 updates to {{ $subscriber->email }}. You can sign up again at any time."
    :action-url="$actionUrl"
    button-label="Unsubscribe"
    method="DELETE"
/>

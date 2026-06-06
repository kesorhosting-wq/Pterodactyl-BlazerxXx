<footer class="footer-v2" style="margin-bottom: 2%">
    @php
        $currentYear = date('Y');
    @endphp

    @if(!empty($footer_text))
        <p class="PageContentBlock___StyledP-sc-kbxq2g-3 copyrightFooter" style="margin-top: 2%; padding-bottom: -5%;">
            {!! $footer_text !!}
            <br />
            <a rel="noopener nofollow noreferrer" href="https://pterodactyl.io/" target="_blank" class="PageContentBlock___StyledA-sc-kbxq2g-4 eOGAqX">
                Pterodactyl®
            </a> 
            © 2015 - {{ $currentYear }}
        </p>
    @else
        <p class="PageContentBlock___StyledP-sc-kbxq2g-3 copyrightFooter" style="margin-top: 2%; padding-bottom: -5%;">
            <a rel="noopener nofollow noreferrer" href="https://euphoriatheme.uk" target="_blank" class="PageContentBlock___StyledA-sc-kbxq2g-4 eOGAqX">
                Powered by Euphoria
            </a> 
            © 2024 - {{ $currentYear }}
            <br />
            <a rel="noopener nofollow noreferrer" href="https://pterodactyl.io/" target="_blank" class="PageContentBlock___StyledA-sc-kbxq2g-4 eOGAqX">
                Pterodactyl®
            </a> 
            © 2015 - {{ $currentYear }}
        </p>
    @endif
</footer>

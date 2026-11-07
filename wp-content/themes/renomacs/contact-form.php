<?php
    /* 
        Template Name: Contact Form
    */
get_header();
?>

<div id="form-spinner" class="form-spinner d-none">
  <div class="spinner"></div>
</div>

<section class="contact-form">
    <div class="container contact-wrapper">
        <div class="content-wrapper">
            <?php echo the_content(); ?>
        </div>

        <div class="thank-you-message d-none" tabindex="-1" role="status">
            <h2>Thank you — Your enquiry has been sent!</h2>
            <p>We have received your request and our team will review your details shortly.</p>
            <p>A RenoMacs representative will contact you within 24 hours to confirm your project information or arrange a free site visit.</p>
        </div>

        <form id="customForm" class="pt-3 form-grid" method="post">
            <div class="form-group">
                <input type="text" id="fullname" name="fullname" required>
                <label for="fullname">Full name*</label>
            </div>

            <div class="form-group">
                <input type="text" id="company" name="company">
                <label for="company">Company</label>
            </div>

            <div class="form-group">
                <input type="tel" id="contact" name="contact" required>
                <label for="contact">Contact number*</label>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Email*</label>
            </div>

            <div class="form-group choices-select">
                <label for="services">Services required (Select one or more)</label>
                <select id="services" name="services[]" multiple>
                    <option value="Partition Installation">Partition Installation</option>
                    <option value="Electrical Wiring">Electrical Wiring</option>
                    <option value="Water Piping">Water Piping</option>
                    <option value="Tiling Works">Tiling Works</option>
                    <option value="Painting">Painting</option>
                    <option value="Plaster Ceiling">Plaster Ceiling</option>
                </select>
            </div>

            <div class="form-group choices-select">
                <label for="projecttype">Project type</label>
                <select id="projecttype" name="projecttype">
                    <option value="" selected disabled hidden>Select a project type</option>
                    <option value="Residential">Residential</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Industrial">Industrial</option>
                </select>
            </div>
            
            <div class="form-group full-width">
                <input type="text" id="address" name="address">
                <label for="address">Address</label>
            </div>

            <div class="form-group">
                <input type="text" id="budget" name="budget">
                <label for="budget">Budget</label>
            </div>

            <div class="form-group">
                <input type="date" id="startdate" name="startdate">
                <label for="startdate">Preferred start date</label>
            </div>


            <div class="form-group full-width">
                <textarea id="description" name="description" rows="3"></textarea>
                <label for="description">Project description</label>
            </div>

            <div class="form-check full-width">
                <input type="checkbox" id="consent" name="consent" value="Yes" required>
                <label for="consent" class="check-label">*I consent to be contacted by RenoMacs regarding my enquiry via phone, WhatsApp, or email, for quotation and service purposes.</label>
            </div>

            <div id="enquiry-error" class="full-width d-none" role="alert" tabindex="-1">
                <p id="enquiry-error-text"></p>
                <p>Your details are still here. Call <a href="tel:+60139936857">+6013-993 6857</a>
                or <a href="https://wa.link/vw98jd" target="_blank" rel="noopener noreferrer">WhatsApp us</a>.</p>
            </div>
            <noscript><p class="full-width">Please enable JavaScript to use this form, or call +6013-993 6857.</p></noscript>
            <div class="form-actions full-width">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
    (() => {
        const endpoint = <?php echo wp_json_encode(rest_url('custom/v1/contact')); ?>;
        const form = document.getElementById('customForm');
        const button = form.querySelector('button[type="submit"]');
        const error = document.getElementById('enquiry-error');
        const spinner = document.getElementById('form-spinner');
        let submitting = false;
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (submitting || !form.reportValidity()) return;
            error.classList.add('d-none');
            submitting = true;
            button.disabled = true;
            button.textContent = 'Sending…';
            form.setAttribute('aria-busy', 'true');
            spinner.classList.remove('d-none');
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 45000);
            let failureMessage = 'We could not confirm delivery. Please contact us directly before retrying to avoid a duplicate enquiry.';
            try {
                const response = await fetch(endpoint, {
                    method: 'POST', body: new FormData(form), signal: controller.signal
                });
                const result = await response.json();
                if (!response.ok || !result || result.result !== 'success') {
                    if ([400, 413, 429, 503].includes(response.status) && typeof result.message === 'string') {
                        failureMessage = result.message;
                    }
                    throw new Error('Delivery not confirmed');
                }
                document.querySelector('.content-wrapper').classList.add('d-none');
                form.classList.add('d-none');
                const thanks = document.querySelector('.thank-you-message');
                thanks.classList.remove('d-none');
                thanks.focus();
            } catch (failure) {
                document.getElementById('enquiry-error-text').textContent = failureMessage;
                error.classList.remove('d-none');
                error.focus();
            } finally {
                clearTimeout(timer);
                submitting = false;
                button.disabled = false;
                button.textContent = 'Submit';
                form.removeAttribute('aria-busy');
                spinner.classList.add('d-none');
            }
        });
    })();
    </script>
</section>

<a href="https://wa.link/vw98jd" target="blank_"><div id="whatsapp-icon"></div></a>
<div id="back-to-top"></div>
<?php
get_footer();
?>

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

        <div class="thank-you-message d-none">
            <h2>Thank you — Your enquiry has been sent!</h2>
            <p>We have received your request and our team will review your details shortly.</p>
            <p>A RenoMacs representative will contact you within 24 hours to confirm your project information or arrange a free site visit.</p>
        </div>

        <form id="customForm" class="pt-3 form-grid">
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

            <div class="form-actions full-width">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        /* Local */
        const endpoint = '../wp-json/custom/v1/contact'
        /* Production */
        // const endpoint = '../wp-json/custom/v1/contact'
        const form = document.getElementById('customForm')

        function showError(input, message) {
            input.classList.add("invalid")

            let error = input.parentElement.querySelector('.error-text')
            if (!error) {
                error = document.createElement("small")
                error.className = "error-text"
                input.parentElement.appendChild(error)
            }
            error.textContent = message
        }

        function clearErrors() {
            document.querySelectorAll('.error-text').forEach(e => e.remove())
            document.querySelectorAll('.invalid').forEach(e => e.classList.remove('invalid'))
        }

        function showSpinner() {
            document.getElementById("form-spinner").classList.remove("d-none");
        }

        function hideSpinner() {
            document.getElementById("form-spinner").classList.add("d-none");
        }

        function thankYouMessage() {
            const contactWrapper = document.querySelector('.contact-wrapper')

            const contentWrapper = document.querySelector('.content-wrapper')
            const form = document.getElementById('customForm')
            const thankYouMessage = document.querySelector('.thank-you-message')

            const footerHeight = document.querySelector('footer').offsetHeight

            contactWrapper.style.height = `calc(100vh - ${footerHeight}px)`


            contentWrapper.classList.add('d-none')            
            form.classList.add('d-none')
            thankYouMessage.classList.remove('d-none')
        }

        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            clearErrors()

            let isValid = true

            const requiredFields = ['fullname', 'email', 'contact']

            for (const name of requiredFields) {
                const input = this[name]
                const value = input.value.trim()
                
                switch (name) {
                    case "fullname":
                        if (!value) {
                            isValid = false
                            showError(input, "Full name is required")
                        }
                        break;
                    case "email":
                        if (!value) {
                            isValid = false
                            showError(input, "Email is required")
                        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            isValid = false
                            showError(input, "Enter a valid email address")
                        }
                        break;
                    case "contact":
                        if (!value) {
                            isValid = false
                            showError(input, "Contact number is required")
                        } else if (!/^\+?[\d\s-]{6,15}$/.test(value)) {
                            isValid = false
                            showError(input, "Enter a valid contact number")
                        }
                        break;
    
                    default:
                        break;
                }
            }

            if (!this.consent.checked) {
                isValid = false;
                showError(this.consent, "You must consent before submitting")
            }

            if (!isValid) return;

            const formData = new FormData(this);
            const RM_SECRET = "<?php echo RM_SECRET; ?>";
            formData.append("secret", RM_SECRET);
            const data = {};

            formData.forEach((value, key) => {
                if (data[key]) {
                    //Handle multi select arrays
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value
                }
            })

            if (data['services[]']) {
                data.services = data['services[]']
                delete data['services[]']
            }

            // const data = Object.fromEntries(formData.entries());
            data.consent = this.consent.checked ? "Yes" : "No";

            showSpinner()

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    body: formData,
                });
                
                const result = await response.json();

                console.log(result);
                console.log(result.result);

                if (result && result.result === "success") {
                    thankYouMessage(); // ✅ only show when success
                }

                this.reset();
                
            } catch (err) {
                alert("⚠️ Something went wrong, contact us at <a href='telno:+6013-9936857' target='blank_'>+6013-993 6857</a> or <a href='wa.link/vw98jd' target='blank_'>WhatsApp</a> us.");
                return
            } finally {
                hideSpinner()
            }
        });
    </script>
</section>

<a href="https://wa.link/vw98jd" target="blank_"><div id="whatsapp-icon"></div></a>
<div id="back-to-top"></div>
<?php
get_footer();
?>
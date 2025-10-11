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
        <?php echo the_content(); ?>

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
                <label for="services">Services required</label>
                <select id="services" name="services[]" multiple>
                    <option value="Partition Installation">Partition Installation</option>
                    <option value="Electrical Wiring">Electrical Wiring</option>
                    <option value="Water Piping">Water Piping</option>
                    <option value="Tiling Works">Tiling Works</option>
                    <option value="Painting">Painting</option>
                    <option value="Plaster Ceiling">Plaster Ceiling</option>
                </select>
            </div>

            <div class="form-group">
                <input type="text" id="projecttype" name="projecttype">
                <label for="projecttype">Project type</label>
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
                <label for="consent" class="check-label">I consent*</label>
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        const endpoint = '/renomacs/wp-json/custom/v1/contact'
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

                this.reset();
                
            } catch (err) {
                alert("⚠️ Something went wrong while submitting.");
                return
            } finally {
                hideSpinner()
            }
        });
    </script>
</section>

<div id="whatsapp-icon"></div>
<div id="back-to-top"></div>
<?php
get_footer();
?>
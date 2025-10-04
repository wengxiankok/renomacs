<?php
    /* 
        Template Name: Contact Form
    */
    get_header();
?>

<section class="contact-form">
    <div class="container contact-wrapper">
        <?php echo the_content(); ?>

        <form id="customForm" class="pt-3 form-grid">
            <div class="form-group">
                <input type="text" id="fullname" name="fullname" required>
                <label for="fullname">Full name</label>
            </div>

            <div class="form-group">
                <input type="text" id="company" name="company">
                <label for="company">Company</label>
            </div>

            <div class="form-group">
                <input type="tel" id="contact" name="contact">
                <label for="contact">Contact number</label>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Email</label>
            </div>

            <div class="form-group">
                <input type="text" id="services" name="services">
                <label for="services">Services required</label>
            </div>

            <div class="form-group">
                <input type="text" id="projecttype" name="projecttype">
                <label for="projecttype">Project type</label>
            </div>

            <div class="form-group full-width">
                <input type="text" id="address" name="address">
                <label for="address">Address</label>
            </div>

            <div class="form-group full-width">
                <textarea id="description" name="description" rows="3"></textarea>
                <label for="description">Project description</label>
            </div>

            <div class="form-group">
                <input type="text" id="budget" name="budget">
                <label for="budget">Budget</label>
            </div>

            <div class="form-group">
                <input type="date" id="startdate" name="startdate">
                <label for="startdate">Preferred start date</label>
            </div>

            <div class="form-check full-width">
                <input type="checkbox" id="consent" name="consent" value="Yes">
                <label for="consent" class="check-label">I consent</label>
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("customForm").addEventListener("submit", function(e) {
            e.preventDefault();

            fetch("https://script.google.com/macros/s/AKfycbxVfHXTmBZ_cG85okPmulGiZqDYmo-PSrzf0gT7yo-MZ3QKl4uRd2PrgNCI4D_-kiHxeQ/exec", {
                    method: "POST",
                    body: JSON.stringify({
                        secret: "renomacs-MFlxhZx4v6xAStQ5ILqRbC7X", // must match your script
                        fullname: this.fullname.value,
                        company: this.company.value,
                        contact: this.contact.value,
                        email: this.email.value,
                        services: this.services.value,
                        projecttype: this.projecttype.value,
                        address: this.address.value,
                        description: this.description.value,
                        budget: this.budget.value,
                        startdate: this.startdate.value,
                        consent: this.consent.checked ? "Yes" : "No"
                    })
                })
                .then(res => res.json())
                .then(response => alert("Thanks, your form was submitted!"))
                .catch(error => alert("Error: " + error.message));
        });
    </script>
</section>

<div id="whatsapp-icon"></div>
<div id="back-to-top"></div>
<?php
get_footer();
?>
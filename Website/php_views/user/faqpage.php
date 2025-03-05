<?php
// Set the page title dynamically
$pageTitle = "User Profile"; 

// Include the header
include('../asset_for_pages/header.php');
?>


<div class="container">
          <div class="page-inner">
            <div class="page-header">
              <h3 class="fw-bold mb-3">FAQs</h3>
              <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                  <a href="#">
                    <i class="icon-home"></i>
                  </a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <!-- <li class="nav-item">
                  <a href="#">User Tables</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li> -->
                <li class="nav-item">
                    <a href="#">FAQs</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
              </ul>
            </div>
            <div class="row"></div>
            <title>FAQ Page</title>
                    <link rel="stylesheet" href="styles.css">
                    <style>
                        .faq-section {
                            font-family: Arial, sans-serif;
                            max-width: 600px;
                            margin: 20px auto;
                            background-color: #f9f9f9;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                        }

                        .faq-section h2 {
                            text-align: center;
                            font-size: 1.8em;
                            margin-bottom: 20px;
                            color: #333;
                        }

                        .faq-section .faq-item {
                            margin-bottom: 10px;
                        }

                        .faq-section .faq-question {
                            width: 100%;
                            padding: 15px;
                            background-color: #0073e6;
                            color: #fff;
                            border: none;
                            border-radius: 5px;
                            font-size: 1.1em;
                            cursor: pointer;
                            text-align: left;
                            position: relative;
                        }

                        .faq-section .faq-question::after {
                            content: '+';
                            position: absolute;
                            right: 20px;
                            font-size: 1.2em;
                            transition: transform 0.2s;
                        }

                        .faq-section .faq-answer {
                            max-height: 0;
                            overflow: hidden;
                            transition: max-height 0.3s ease;
                            background-color: #f1f1f1;
                            padding: 0 15px;
                            border-radius: 5px;
                        }

                        .faq-section .faq-answer p {
                            padding: 15px 0;
                            font-size: 1em;
                            color: #555;
                        }

                        .faq-section .faq-question.active::after {
                            content: '-';
                            transform: rotate(45deg);
                        }

                        .faq-section .faq-question.active + .faq-answer {
                            max-height: 200px;
                        }

                    </style>
                    <div class="faq-section">
                        <h2>Frequently Asked Questions</h2>
                        <div class="faq-item">
                          <button class="faq-question">1. How do I reset my password?</button>
                          <div class="faq-answer">
                            <p>Click on the "Forgot Password?" link on the login page. Enter your email address, and you will receive a password reset link in your inbox.</p>
                          </div>
                        </div>
                        <div class="faq-item">
                          <button class="faq-question">2. What should I do if I don’t receive the confirmation email?</button>
                          <div class="faq-answer">
                            <p>Check your spam or junk folder. If it’s not there, try resending the confirmation email from the login page or contact support.</p>
                          </div>
                        </div>
                        <div class="faq-item">
                          <button class="faq-question">3. Can I change my email address?</button>
                          <div class="faq-answer">
                            <p>Yes! Go to your Profile settings, and you’ll find an option to update your email address. Make sure to confirm the new email.</p>
                          </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">4. How do I delete my account?</button>
                            <div class="faq-answer">
                                <p>To delete your account, please contact our support team. They will guide you through the process and ensure that all your data is handled securely.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">5. Is my personal information safe?</button>
                            <div class="faq-answer">
                                <p>Absolutely! We take your privacy seriously and employ industry-standard security measures to protect your data. Please refer to our Privacy Policy for more details.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">6. What if I encounter a technical issue?</button>
                            <div class="faq-answer">
                                <p>If you experience any technical difficulties, please visit our Help Center for troubleshooting tips or submit a ticket to our support team for further assistance.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">7. Are there any fees associated with using your service?</button>
                            <div class="faq-answer">
                                <p>Our service is free to use with optional premium features available for a fee. You can explore our pricing plans on the Payments page for more details.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">8. How can I contact customer support?</button>
                            <div class="faq-answer">
                                <p>You can reach our customer support team via the "Contact Us" section on our website. We are available through email, chat, and phone support during business hours.</p> 
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">9. Can I cancel my subscription at any time?</button>
                            <div class="faq-answer">
                                <p>Yes, you can cancel your subscription at any time through your account settings. Please note that cancellation will take effect at the end of your current billing cycle.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-question">10. Do you offer a money-back guarantee?</button>
                            <div class="faq-answer">
                                <p>Yes, we offer a 30-day money-back guarantee on our premium plans. If you are not satisfied with our service, you can request a refund within 30 days of your purchase.</p>
                            </div>
                        </div>


                      </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                        const questions = document.querySelectorAll(".faq-section .faq-question");

                            questions.forEach((question) => {
                            question.addEventListener("click", () => {
                            question.classList.toggle("active");
                                const answer = question.nextElementSibling;
                        if (question.classList.contains("active")) {
                            answer.style.maxHeight = answer.scrollHeight + "px";
                            } else {
                                        answer.style.maxHeight = "0";
                                    }
                                    });
                            });
                        });

                    </script>
                    <script>
      $(document).ready(function () {
        $("#basic-datatables").DataTable({});

        $("#multi-filter-select").DataTable({
          pageLength: 5,
          initComplete: function () {
            this.api()
              .columns()
              .every(function () {
                var column = this;
                var select = $(
                  '<select class="form-select"><option value=""></option></select>'
                )
                  .appendTo($(column.footer()).empty())
                  .on("change", function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                    column
                      .search(val ? "^" + val + "$" : "", true, false)
                      .draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    select.append(
                      '<option value="' + d + '">' + d + "</option>"
                    );
                  });
              });
          },
        });

        // Add Row
        $("#add-row").DataTable({
          pageLength: 5,
        });

        var action =
          '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

        $("#addRowButton").click(function () {
          $("#add-row")
            .dataTable()
            .fnAddData([
              $("#addName").val(),
              $("#addPosition").val(),
              $("#addLocation").val(),
              action,
            ]);
          $("#addRowModal").modal("hide");
        });
      });
    </script>      

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>
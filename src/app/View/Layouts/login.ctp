<!DOCTYPE html>
<html>

<head>
  <?php echo $this->Html->charset(); ?>
  <title>
    <?php echo $title_for_layout; ?>
  </title>
  <?php

  echo $this->Html->meta('icon', $this->Html->url('/favicon.png'));

  echo $this->Html->css('bootstrap');
  echo $this->Html->css('bootstrap-responsive');
  echo $this->Html->css('general');
  echo $this->Html->css('colors');
  echo $this->Html->css('login');

  echo $this->fetch('meta');
  echo $this->fetch('css');
  echo $this->fetch('script');
  ?>
  <?php
  echo $this->Html->script('jquery-2.0.3.min');
  echo $this->Html->script('bootstrap.min');
  echo $this->Html->script('public.js');
  ?>
  <link rel="stylesheet" href="https://yandex.st/highlightjs/8.0/styles/default.min.css">
  <script src="https://yandex.st/highlightjs/8.0/highlight.min.js"></script>
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
</head>
<?php
//Random background image
?>

<body class="boxes-page">
  <div class="single-box login content-login img<?php echo rand(1, 4); ?>" style="min-height: 620px">
    <?php echo $this->fetch('content'); ?>
  </div>
  <div id="contact" class="single-box contact-box" style="min-height: 710px">
    <div class="contact-content">
      <div class="container-fluid content-about">
        <div class="row-fluid">
          <div class="span12">
            <div class="container">
              <div class="row">
                <div class="span12">
                  <h1><?php echo __("about"); ?>.run.codes</h1>
                  <p class="home-about-text">
                    run.codes é um sistema de submissão e correção automática de exercícios de programação, com suporte a diversas linguagens como Java, C/C++, R, Octave, entre outras. Apenas o run.codes fornece correção de exercícios de programação com resposta ao aluno praticamente instantânea, a validação de resultados através de complexos casos de teste, utilizando margem de erro definida ou comparação de arquivos binários e análise de plágio entre os códigos submetidos. Dessa maneira, o run.codes pode ser uma importante ferramenta para alunos e professores no aprendizado de programação. Deseja conhecer mais e saber como utilizar o run.codes em seu curso? Entre em contato pelo formulário abaixo:
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container-fluid content-contact">
        <div class="row-fluid">
          <div class="span12">
            <div class="container">
              <div class="row">
                <div class="span12">
                  <h1><?php echo __("talk.with.us"); ?>!</h1>
                </div>
              </div>
              <div class="row">
                <div class="span12" id="emailMessage">

                </div>
              </div>
              <div class="row">
                <div class="span12">
                  <form id="homeContactForm" class="home-contact-form">
                    <div class="row-fluid">
                      <div class="span6">
                        <input name="data[Contact][name]" class="form-control contact-input span12" type="text" placeholder="<?php echo __("Name"); ?>" required="required">
                        <input name="data[Contact][email]" class="form-control contact-input span12" type="email" placeholder="<?php echo __("E-Mail"); ?>" required="required">
                        <input name="data[Contact][subject]" class="form-control contact-input span12" type="text" placeholder="<?php echo __("Assunto"); ?>" required="required">
                      </div>
                      <div class="span6">
                        <textarea name="data[Contact][message]" class="form-control contact-textarea span12" placeholder="<?php echo __("Mensagem"); ?>" pattern=".{20,}" title="<?php echo __("Your message should have at least 20 characters"); ?>" required="required"></textarea>
                      </div>
                    </div>
                    <div class="row-fluid">
                      <div class="span12">
                        <input class="form-control contact-submit btn-block" id="btnFormContactSubmit" type="submit" value="<?php echo __("Submit"); ?>">
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="single-box faq-box" id="faq">
    <div class="faq-content" style="min-height: 100%">
      <div class="container-fluid content-faq">
        <div class="row-fluid">
          <div class="span12">
            <div class="container">
              <div class="row">
                <div class="span12">
                  <h1><?php echo __("faq"); ?></h1>
                  <p class="home-about-text">
                    <?php echo __("Dúvidas sobre o run.codes? Respondemos algumas perguntas que você pode ter"); ?>
                  </p>
                  <ul>
                    <?php foreach ($questions as $question) : ?>
                      <li><a href="#" class="faq-link" data-question="<?php echo $question['Question']['id']; ?>"><?php echo $question['Question']['title']; ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="modalQuestion" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalQuestionLabel" aria-hidden="true" style="width: 960px; margin-left: -480px">

  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#homeContactForm").on("submit", function(e) {
        e.preventDefault();
        $.post("/Pages/contact", $("#homeContactForm").serialize(), function(data) {
          $("#emailMessage").html(data);
        });
      });
      $(".faq-link").on("click", function(e) {
        e.preventDefault();
        $("#modalQuestion").html("");
        $.get("/Questions/modal/" + $(this).attr('data-question'), null, function(data) {
          $("#modalQuestion").html(data);
          $('#modalQuestion').modal('show');
        });
      });
    });
  </script>
</body>

</html>

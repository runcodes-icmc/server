<?php
$domain = (new ConfigLoader())->configs['RUNCODES_DOMAIN'];
?>
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      Olá <strong style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;"><?php echo $user_name; ?></strong>,
    </td>
  </tr>
  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      Utilizar o run.codes é uma tarefa simples. Você deve se cadastrar no sistema <a href="<?= $domain ?>/">run.codes</a> normalmente. Ao realizar o login pela primeira vez, você deverá escolher sua instituição de ensino e número de matrícula.
    </td>
  </tr>

  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      <strong>Você é um professor ou coordenador de curso?</strong> <br>
      Por padrão, todo cadastro no sistema tem status de “Aluno”. Assim, todo professor que desejar utilizar a ferramenta deve requerer o status de “Professor” enviando para runcodes@icmc.usp.br um comprovante (este pode ser, por exemplo, um link para o seu site pessoal na instituição). Caso sua instituição de ensino não esteja listada no momento que você entrar no sistema pela primeira vez, envie-nos um email para ativarmos no sistema!
    </td>
  </tr>

  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      <strong>Você é um aluno?</strong> <br>
      O run.codes foi projetado para o ambiente de salas de aula. Dessa maneira, é necessário um primeiro contato de um professor ou coordenador da instituição de ensino. Qualquer um pode ter um cadastro mas não tem sentido um aluno se cadastrar se nenhuma disciplina da sua universidade utiliza o run.codes. Se você é um aluno e ficou interessado, pedimos que recomende ao seus professores para que eles conheçam o run.codes.
    </td>
  </tr>
  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      <a href="<?= $domain ?>/Users/add/<?php echo $user_email; ?>" class="btn-primary" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; background: #348eda; margin: 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">
        Cadastre-se Agora!
      </a>
    </td>
  </tr>
  <tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <td class="content-block" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
      Obrigado por se interessar em utilizar o run.codes. <br>
      Este é um email automático, mas estamos a disposição caso necessite de maiores detalhes! Você pode respondê-lo com qualquer dúvida que entraremos em contato. <br>
      Para mais informações acesse: <a href="http://we.run.codes/">http://we.run.codes</a> <br>
      Att.,
    </td>
  </tr>
</table>

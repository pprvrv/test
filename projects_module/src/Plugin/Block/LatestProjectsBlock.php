<?php

namespace Drupal\projects_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "latest_projects_block",
 *   admin_label = @Translation("Последние проекты")
 * )
 */
class LatestProjectsBlock extends BlockBase
{

  public function build()
  {

    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'proekt');
    $query->condition('status', 1);
    $query->sort('created', 'DESC');
    $query->range(0, 3);

    $nids = $query->accessCheck(FALSE)->execute();

    if (empty($nids)) {
      return [
        '#markup' => '<div class="latest-projects-block">
          <h2>Последние проекты</h2>
          <p>GПроектов  нет.</p>
        </div>'
      ];
    }

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $output = '<div class="latest-projects-block">
    <div class="container">
      <h2>Последние проекты</h2>
      <div class="projects-list">';

    foreach ($nodes as $node) {
      $title = $node->getTitle();
      $url = $node->toUrl()->toString();


      $description = '';
      if (isset($node->field_opisanie->value) && !empty($node->field_opisanie->value)) {
        $description = '<div class="project-description">' . $node->field_opisanie->value . '</div>';
      }


      $date_html = '';
      if (isset($node->field_data_okonchaniya->value) && !empty($node->field_data_okonchaniya->value)) {
        $date_value = $node->field_data_okonchaniya->value;

        $date_only = substr($date_value, 0, 10);
        $date_parts = explode('-', $date_only);
        if (count($date_parts) === 3) {
          $formatted_date = $date_parts[2] . '.' . $date_parts[1] . '.' . $date_parts[0];
          $date_html = '<div class="project-end-date">
            <strong>Дата окончания:</strong> ' . $formatted_date . '
          </div>';
        } else {

          $date_html = '<div class="project-end-date">
            <strong>Дата окончания:</strong> ' . $date_only . '
          </div>';
        }
      }

      $image_html = '';
      if (isset($node->field_izobrazhenie->target_id)) {
        $media = \Drupal\media\Entity\Media::load($node->field_izobrazhenie->target_id);

        if ($media && $media->hasField('field_media_image')) {
          $file = $media->field_media_image->entity;

          if ($file) {
            $image_uri = $file->getFileUri();
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($image_uri);

            $image_html = '<div class="project-image">
              <img src="' . $image_url . '" alt="' . $title . '">
            </div>';
          }
        }
      }

      $output .= '
      <div class="project-item">
        ' . $image_html . '
        <div class="project-content">
          <h3 class="project-item__title"><a href="' . $url . '">' . $title . '</a></h3>
          ' . $date_html . '
          ' . $description . '
          
        </div>
      </div>';
    }

    $output .= '</div></div></div>';


    return [
      '#markup' => $output,
      '#attached' => [
        'library' => ['projects_module/latest_projects_block'],
      ],
    ];
  }
}

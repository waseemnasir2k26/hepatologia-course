<?php
/**
 * Course config — DB-backed (CPT) with hardcoded "Cirrosis 360" seed for first install.
 * Public API:
 *   C360_LMS_Config::courses() → array of course arrays
 *   C360_LMS_Config::course( $slug_or_id ) → single course array
 *   C360_LMS_Config::lessons( $course_id = 0 )
 *   C360_LMS_Config::lesson( $slug_or_id )
 *   C360_LMS_Config::quiz( $lesson_slug_or_id )
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Config {

    /** Build a course array from a WP_Post. */
    private static function from_course_post( $post ) {
        if ( ! $post || $post->post_type !== C360_LMS_CPT::CPT_COURSE ) return null;
        $days = (int) get_post_meta( $post->ID, '_c360_duration_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        return array(
            'id'       => (int) $post->ID,
            'slug'     => $post->post_name,
            'title'    => $post->post_title,
            'subtitle' => (string) get_post_meta( $post->ID, '_c360_subtitle', true ),
            'description' => $post->post_content,
            'price'    => (string) get_post_meta( $post->ID, '_c360_price', true ),
            'currency' => (string) get_post_meta( $post->ID, '_c360_currency', true ) ?: 'PEN',
            'duration_days' => $days,
            'duration_label' => self::days_to_label( $days ),
            'lessons'  => self::lessons( $post->ID ),
        );
    }

    private static function from_lesson_post( $post ) {
        if ( ! $post || $post->post_type !== C360_LMS_CPT::CPT_LESSON ) return null;
        $has_quiz_meta = (int) get_post_meta( $post->ID, '_c360_has_quiz', true );
        return array(
            'id'        => (int) $post->ID,
            'slug'      => $post->post_name,
            'title'     => $post->post_title,
            'subtitle'  => (string) get_post_meta( $post->ID, '_c360_subtitle', true ),
            'type'      => (string) get_post_meta( $post->ID, '_c360_type', true ) ?: 'module',
            'order'     => (int) $post->menu_order,
            'vimeo'     => (string) get_post_meta( $post->ID, '_c360_vimeo_id', true ),
            'pdf'       => (string) get_post_meta( $post->ID, '_c360_pdf_url', true ),
            'has_quiz'  => $has_quiz_meta === 1,
            'course_id' => (int) get_post_meta( $post->ID, '_c360_course_id', true ),
        );
    }

    public static function courses() {
        $posts = get_posts( array(
            'post_type'      => C360_LMS_CPT::CPT_COURSE,
            'numberposts'    => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'suppress_filters' => false,
        ) );
        return array_filter( array_map( array( __CLASS__, 'from_course_post' ), $posts ) );
    }

    public static function course( $key ) {
        if ( is_numeric( $key ) ) {
            $post = get_post( (int) $key );
        } else {
            $posts = get_posts( array(
                'post_type'   => C360_LMS_CPT::CPT_COURSE,
                'name'        => sanitize_title( $key ),
                'numberposts' => 1,
                'post_status' => 'publish',
            ) );
            $post = $posts ? $posts[0] : null;
        }
        return $post ? self::from_course_post( $post ) : null;
    }

    public static function default_course() {
        $all = self::courses();
        return $all ? reset( $all ) : null;
    }

    public static function lessons( $course_id = 0 ) {
        $args = array(
            'post_type'      => C360_LMS_CPT::CPT_LESSON,
            'numberposts'    => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        );
        if ( $course_id ) {
            $args['meta_query'] = array( array( 'key' => '_c360_course_id', 'value' => (int) $course_id ) );
        }
        $posts = get_posts( $args );
        $out = array();
        foreach ( $posts as $p ) {
            $row = self::from_lesson_post( $p );
            if ( $row ) $out[ $row['slug'] ] = $row;
        }
        return $out;
    }

    public static function lesson( $key, $course_id = 0 ) {
        if ( is_numeric( $key ) ) {
            $post = get_post( (int) $key );
        } else {
            $args = array(
                'post_type'   => C360_LMS_CPT::CPT_LESSON,
                'name'        => sanitize_title( $key ),
                'numberposts' => 1,
                'post_status' => 'publish',
            );
            if ( $course_id ) {
                $args['meta_query'] = array( array( 'key' => '_c360_course_id', 'value' => (int) $course_id ) );
            }
            $posts = get_posts( $args );
            $post = $posts ? $posts[0] : null;
        }
        return $post ? self::from_lesson_post( $post ) : null;
    }

    public static function quiz( $lesson_key ) {
        $lesson = self::lesson( $lesson_key );
        if ( ! $lesson || ! $lesson['has_quiz'] ) return null;
        $raw = get_post_meta( $lesson['id'], '_c360_quiz', true );
        $data = $raw ? json_decode( $raw, true ) : null;
        if ( ! is_array( $data ) || ! $data ) return null;
        $pass = (int) get_post_meta( $lesson['id'], '_c360_pass_score', true ) ?: 70;
        return array(
            'lesson_id'  => $lesson['id'],
            'title'      => 'Quiz — ' . $lesson['title'],
            'pass_score' => $pass,
            'questions'  => $data,
        );
    }

    public static function lesson_pdf_url( $lesson_key ) {
        $lesson = self::lesson( $lesson_key );
        return $lesson ? $lesson['pdf'] : '';
    }

    private static function days_to_label( $days ) {
        if ( $days >= 365 ) return ( intval( $days / 365 ) ) . ' año(s) de acceso';
        if ( $days >= 30 )  return ( intval( $days / 30 ) ) . ' meses de acceso';
        return $days . ' días de acceso';
    }

    /**
     * Seed the hardcoded "Cirrosis 360" course on activation if no courses exist.
     * Idempotent: only runs when CPT empty.
     */
    public static function maybe_seed_default() {
        $existing = get_posts( array(
            'post_type'      => C360_LMS_CPT::CPT_COURSE,
            'numberposts'    => 1,
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );
        if ( $existing ) return;

        $cid = wp_insert_post( array(
            'post_type'   => C360_LMS_CPT::CPT_COURSE,
            'post_status' => 'publish',
            'post_title'  => 'Cirrosis 360 — Programa Completo',
            'post_name'   => 'cirrosis-360',
            'post_content' => 'Programa educativo del Dr. Julio Santiago Marcelo para pacientes y familias que viven con cirrosis hepática. 3 módulos clínicos + 3 bonos prácticos + 1 testimonial.',
        ), true );
        if ( is_wp_error( $cid ) || ! $cid ) return;

        update_post_meta( $cid, '_c360_subtitle', 'Entienda y controle su cirrosis paso a paso' );
        update_post_meta( $cid, '_c360_price', 'S/ 247' );
        update_post_meta( $cid, '_c360_currency', 'PEN' );
        update_post_meta( $cid, '_c360_duration_days', C360_LMS_EXPIRY_DAYS );

        $seed = self::seed_lessons();
        $order = 0;
        foreach ( $seed as $l ) {
            $order++;
            $lid = wp_insert_post( array(
                'post_type'   => C360_LMS_CPT::CPT_LESSON,
                'post_status' => 'publish',
                'post_title'  => $l['title'],
                'post_name'   => $l['slug'],
                'menu_order'  => $order,
            ) );
            if ( ! $lid || is_wp_error( $lid ) ) continue;
            update_post_meta( $lid, '_c360_course_id', $cid );
            update_post_meta( $lid, '_c360_subtitle', $l['sub'] );
            update_post_meta( $lid, '_c360_type', $l['type'] );
            update_post_meta( $lid, '_c360_vimeo_id', $l['vimeo'] );
            update_post_meta( $lid, '_c360_has_quiz', $l['quiz'] ? 1 : 0 );
            update_post_meta( $lid, '_c360_pass_score', 70 );
            if ( ! empty( $l['pdf'] ) ) update_post_meta( $lid, '_c360_pdf_url', $l['pdf'] );
            if ( ! empty( $l['qz'] ) ) {
                // CRITICAL: JSON_UNESCAPED_UNICODE prevents ¿ escapes that WP slash-handling strips.
                // wp_slash() pre-escapes so update_post_meta's internal unslash leaves UTF-8 intact.
                $json = wp_json_encode( $l['qz'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                update_post_meta( $lid, '_c360_quiz', wp_slash( $json ) );
            }
        }
    }

    /**
     * Canonical lesson seed data. 3 modules (4 quiz Qs each) + 3 bonuses (Bono 1+2 have 4-Q quizzes, Bono 3 video-only).
     * PDF URLs relative to wp-content/uploads/ — adjust if Media Library path differs.
     * Quiz content sourced from client doc CUESTIONARIOS CIRROSIS 360.docx (2026-05-21).
     */
    private static function seed_lessons() {
        $upload_base = wp_upload_dir();
        // PDFs uploaded by Waseem 2026-05-21 → /uploads/2026/05/ (date-based default WP path).
        $pdf_url = trailingslashit( $upload_base['baseurl'] ) . '2026/05/';

        return array(
            array( 'slug'=>'mod1','title'=>'Módulo 1 — Descompensaciones Evitables','sub'=>'Aprenda a reconocer las señales de alerta','type'=>'module','vimeo'=>'1180151832','quiz'=>true,
                'pdf'=> $pdf_url . 'PDF-TEMA-1-DESCOMPENSACIONES.pdf',
                'qz' => array(
                    array( 'q'=>'Carlos tiene cirrosis y ha decidido no cenar para intentar bajar de peso más rápido. Pasa desde las 7:00 p.m. hasta las 8:00 a.m. del día siguiente sin ingerir alimentos. Según las recomendaciones médicas, ¿cuál es el riesgo de este comportamiento?','options'=>array(
                        'Es una práctica recomendada para permitir que el hígado descanse del proceso de digestión.',
                        'Provoca una rápida pérdida de masa muscular, similar a que una persona sana no coma por 2 o 3 días.',
                        'El riesgo es mínimo si compensa las calorías en el almuerzo del día siguiente.',
                        'Ayuda a reducir la acumulación de líquido en el abdomen al ingerir menos sólidos.',
                        'Solo es peligroso si Carlos también padece de diabetes tipo 1.',
                    ),'correct'=>1 ),
                    array( 'q'=>'María siente un fuerte dolor de rodilla y quiere tomar algo para aliviarlo. En su botiquín tiene los siguientes medicamentos. ¿Cuál de ellos debe evitar por completo para no arriesgarse a una hemorragia digestiva?','options'=>array(
                        'Usar una rodillera',
                        'Paracetamol (Panadol) de 500 mg.',
                        'Ibuprofeno o Naproxeno.',
                        'Antigripales comunes sin antiinflamatorios.',
                        'Compresas de agua caliente.',
                    ),'correct'=>2 ),
                    array( 'q'=>'Juan nota que su abdomen está muy hinchado por el líquido (ascitis) y tiene mucha sed. Su familia le dice que no tome agua para no "llenarse más". ¿Cuál es la conducta correcta según la clase?','options'=>array(
                        'Solo puede tomar agua si la mezcla con hierbas medicinales.',
                        'Debe limitar su consumo de agua a menos de medio litro por día.',
                        'Debe tomar diuréticos sin receta antes de beber cualquier líquido.',
                        'Juan puede y debe tomar agua; lo que debe hacer es disminuir el consumo de sal en las comidas.',
                        'Debe dejar de tomar agua y sustituirla por jugos verdes para desintoxicar el hígado.',
                    ),'correct'=>3 ),
                    array( 'q'=>'Luis presenta una fiebre. Sabiendo que tiene cirrosis, ¿qué acción debe tomar su familia de inmediato?','options'=>array(
                        'Administrarle una inyección de Diclofenaco.',
                        'Acudir a urgencias, ya que la fiebre es un signo de alarma de infección que puede descompensarlo.',
                        'Darle antibióticos que sobraron de una infección anterior.',
                        'Esperar 24 horas para ver si la fiebre cede con baños de agua fría.',
                        'Darle una dosis de 4 gramos de Paracetamol para bajar la fiebre rápido.',
                    ),'correct'=>1 ),
                ) ),
            array( 'slug'=>'mod2','title'=>'Módulo 2 — Nutrición Clave','sub'=>'Plan de alimentación para cirrosis','type'=>'module','vimeo'=>'1147358050','quiz'=>true,
                'pdf'=> $pdf_url . 'PDF-TEMA-2-NUTRICION.pdf',
                'qz' => array(
                    array( 'q'=>'Juan cena habitualmente a las 8 p.m. y desayuna al día siguiente a las 8 a.m. Según las recomendaciones para la cirrosis, ¿qué está ocurriendo con su reserva de energía?','options'=>array(
                        'No sucede nada relevante, ya que el cuerpo humano puede aguantar hasta 24 horas sin alimento sin perder músculo.',
                        'Su cuerpo está usando el exceso de grasa para obtener energía sin afectar la salud.',
                        'Su organismo está empezando a "comerse" su propio músculo para poder sobrevivir al ayuno.',
                        'Este periodo de ayuno es beneficioso porque permite que el hígado descanse del proceso de filtrado.',
                        'Su hígado tiene reservas suficientes para aguantar hasta 14 horas.',
                    ),'correct'=>2 ),
                    array( 'q'=>'María quiere preparar su "adicional nocturno" antes de dormir para evitar el ayuno prolongado. ¿Cuál de estas opciones cumple con la regla de oro nutricional?','options'=>array(
                        'Un plato de ensalada de lechuga y tomate sin aliño.',
                        'Un vaso grande de jugo de naranja recién exprimido.',
                        'Un sándwich de pan de molde con pollo deshilachado.',
                        'Una taza de mazamorra morada tibia.',
                        'Una porción de queso amarillo madurado con galletas integrales.',
                    ),'correct'=>2 ),
                    array( 'q'=>'Carlos tiene ascitis (líquido en el abdomen) y su familia le prepara comida totalmente sin sal. Él ha perdido el apetito y está bajando de peso. ¿Cuál es la recomendación correcta según el material?','options'=>array(
                        'Debe eliminar también el consumo de agua, limitándose a medio litro por día.',
                        'Debe seguir comiendo sin nada de sal, aunque no le guste, para que el líquido desaparezca rápido.',
                        'Puede consumir hasta 1 cucharadita al ras de sal (5 gramos) al día para dar sabor a sus comidas.',
                        'Puede reemplazar la sal por cubitos de caldo concentrado para mejorar el sabor.',
                        'La sal no tiene ninguna relación con la ascitis, por lo que puede comer con sal normal.',
                    ),'correct'=>2 ),
                    array( 'q'=>'Para la cena de Navidad, la familia quiere incluir al paciente con cirrosis en la celebración. ¿Qué ajuste es necesario realizar en su plato?','options'=>array(
                        'Puede comer pavo al horno, pero debe retirarse estrictamente la piel tostada.',
                        'Solo puede comer frutas picadas durante la cena para no sobrecargar el hígado.',
                        'Se le debe servir una copa de vino tinto, ya que es bueno para el corazón.',
                        'Puede consumir ensaladas con mayonesa o cremas siempre que sean bajas en sal.',
                        'Debe comer su panetón y chocolate caliente a la medianoche con el resto de la familia.',
                    ),'correct'=>0 ),
                ) ),
            array( 'slug'=>'mod3','title'=>'Módulo 3 — El Semáforo de la Cirrosis','sub'=>'Sistema de monitoreo diario','type'=>'module','vimeo'=>'1163996678','quiz'=>true,
                'pdf'=> $pdf_url . 'PDF-TEMA-3-SEMAFORO.pdf',
                'qz' => array(
                    array( 'q'=>'Durante la madrugada, el paciente comienza a vomitar sangre. ¿Cuál es el procedimiento a seguir?','options'=>array(
                        'Darle de beber mucha agua fría para detener el sangrado interno.',
                        'Aumentar la dosis de lactulosa para que elimine la sangre por las heces.',
                        'Llamar al médico de cabecera para pedir una cita prioritaria para la tarde.',
                        'Darle un protector gástrico como Omeprazol y esperar a que amanezca.',
                        'Trasladarlo de inmediato a emergencia.',
                    ),'correct'=>4 ),
                    array( 'q'=>'El cuidador principal se siente agotado, no ha dormido bien y se siente deprimido por la carga del cuidado. Según las reglas clave, ¿qué impacto tiene esto?','options'=>array(
                        'Se debe contratar a un enfermero y dejar que el familiar se desentienda por completo.',
                        'Si el cuidador colapsa, el "semáforo se apaga", dejando al paciente desprotegido; su descanso es prioridad.',
                        'El cuidador debe aprender medicina compleja para sentirse más seguro y menos estresado.',
                        'Ninguno, el cuidador debe sacrificarse porque la salud del paciente es la única prioridad.',
                        'Es una situación normal que no requiere atención médica ni familiar.',
                    ),'correct'=>1 ),
                    array( 'q'=>'Su familiar con cirrosis se siente muy bien hoy: conversa, está orientado y ha dormido bien. Sin embargo, comenta que como ya se siente sano, quiere dejar de tomar el carvedilol y los diuréticos. ¿Cuál es la acción correcta?','options'=>array(
                        'Darle hierbas naturales como diente de león para que el hígado descanse de los químicos.',
                        'Llevarlo de inmediato a urgencias porque querer dejar la medicación es un signo de confusión.',
                        'Permitir que descanse de las pastillas unos días para evitar que se canse de la medicación.',
                        'Mantener la disciplina y continuar con toda la medicación según lo recetado.',
                        'Sustituir las pastillas por una dieta sin nada de sal para compensar la falta de medicina.',
                    ),'correct'=>3 ),
                    array( 'q'=>'Usted nota que el paciente hoy está más callado de lo habitual, se demora en responder preguntas simples y ayer no pudo ir al baño en todo el día. ¿En qué zona del semáforo se encuentra?','options'=>array(
                        'Zona Verde, porque todavía puede hablar y no tiene fiebre.',
                        'Zona Amarilla, requiere ajustar la lactulosa y contactar al médico en menos de 48 horas.',
                        'Zona de Nutrición, solo necesita comer más fibra para solucionar el estreñimiento.',
                        'Zona de Observación, solo hay que esperar a la cita del próximo mes para comentarlo.',
                        'Zona Roja, debe llamar a una ambulancia inmediatamente para que lo intuben.',
                    ),'correct'=>1 ),
                ) ),
            array( 'slug'=>'bono1','title'=>'Bono 1 — El Mapa de Alimentos','sub'=>'Guía visual de alimentos seguros','type'=>'bonus','vimeo'=>'1181683995','quiz'=>true,
                'pdf'=> $pdf_url . 'BONO-1-Mapa-Alimentacion.pdf',
                'qz' => array(
                    array( 'q'=>'¿Cuál es la distribución porcentual recomendada para el plato ideal de un paciente con cirrosis?','options'=>array(
                        '40% proteínas, 40% carbohidratos y 20% grasas.',
                        '33% de cada grupo alimenticio.',
                        '50% carbohidratos, 25% proteínas y 25% grasas saludables.',
                        '60% carbohidratos, 20% proteínas y 20% grasas.',
                    ),'correct'=>2 ),
                    array( 'q'=>'¿Cuál es la regla de oro respecto a la compra de productos lácteos para estos pacientes?','options'=>array(
                        'Se pueden consumir siempre que se mantengan en refrigeración constante.',
                        'Deben ser quesos frescos artesanales por ser más naturales.',
                        'Deben ser exclusivamente productos derivados de leche de cabra.',
                        'Deben venir en paquete cerrado de fábrica y estar pasteurizados.',
                    ),'correct'=>3 ),
                    array( 'q'=>'¿Cuál es la recomendación correcta para el uso de aceite de oliva en la dieta?','options'=>array(
                        'Ambos tipos pierden sus propiedades si se almacenan por más de un mes.',
                        'El regular es para cocinar y el extra virgen es para consumo crudo.',
                        'El extra virgen es el mejor para freír alimentos a altas temperaturas.',
                        'Se debe evitar cualquier tipo de aceite de oliva si el paciente tiene diabetes.',
                    ),'correct'=>1 ),
                    array( 'q'=>'¿Cuál es el "secreto" sugerido para cocinar con poca sal sin perder el sabor?','options'=>array(
                        'Agregar la sal al final de la cocción.',
                        'Sustituir la sal por salsa de soja o consomé en polvo.',
                        'Usar solo sal marina o del Himalaya al principio del guiso.',
                        'Hervir los alimentos con abundante sal y luego enjuagarlos.',
                    ),'correct'=>0 ),
                ) ),
            array( 'slug'=>'bono2','title'=>'Bono 2 — Signos Vitales Paso a Paso','sub'=>'Cómo medir y registrar signos vitales','type'=>'bonus','vimeo'=>'1181686811','quiz'=>true,
                'pdf'=> $pdf_url . 'BONO-2-Signos-Vitales.pdf',
                'qz' => array(
                    array( 'q'=>'Si al medir la temperatura de un paciente en la axila el termómetro marca 37.5°C, ¿cuál es la temperatura real del paciente?','options'=>array(
                        '38.5°C',
                        '37.0°C',
                        '38.0°C',
                        '37.5°C',
                    ),'correct'=>2 ),
                    array( 'q'=>'¿Cuál es el rango de pulsaciones considerado normal para un adulto en reposo?','options'=>array(
                        'De 70 a 110 latidos por minuto.',
                        'De 80 a 120 latidos por minuto.',
                        'De 60 a 100 latidos por minuto.',
                        'De 50 a 90 latidos por minuto.',
                    ),'correct'=>2 ),
                    array( 'q'=>'¿Cuál es la técnica recomendada para contar la frecuencia respiratoria sin alterar el resultado?','options'=>array(
                        'Pedirle al paciente que respire profundamente durante un minuto.',
                        'Utilizar un estetoscopio anunciando que se medirán los pulmones.',
                        'Contar las inhalaciones y exhalaciones como dos respiraciones distintas.',
                        'Simular que se toma el pulso mientras se observa discretamente el pecho o abdomen.',
                    ),'correct'=>3 ),
                    array( 'q'=>'¿Cuál es la frecuencia respiratoria normal en un minuto para un paciente adulto?','options'=>array(
                        'De 12 a 18 respiraciones.',
                        'De 10 a 15 respiraciones.',
                        'De 20 a 25 respiraciones.',
                        'De 15 a 20 respiraciones.',
                    ),'correct'=>0 ),
                ) ),
            array( 'slug'=>'bono3','title'=>'Bono 3 — El Método Kardex','sub'=>'Organización segura de medicación','type'=>'bonus','vimeo'=>'1181691449','quiz'=>false,
                'pdf'=> $pdf_url . 'BONO-3-Metodo-Kardex.pdf' ),
        );
    }
}

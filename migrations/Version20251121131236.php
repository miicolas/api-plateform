<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration - Creates all tables for the application
 */
final class Version20251121131236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates user, topic, post, like, media, and follow tables';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE "user" (
            id SERIAL NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            firstname VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');

        // Create topic table
        $this->addSql('CREATE TABLE topic (
            id SERIAL NOT NULL,
            title VARCHAR(255) NOT NULL,
            posts_count INT DEFAULT 0 NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2DE54242B36786B ON topic (title)');

        // Create post table
        $this->addSql('CREATE TABLE post (
            id SERIAL NOT NULL,
            author_id INT NOT NULL,
            topic_id INT DEFAULT NULL,
            content VARCHAR(500) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            likes_count INT DEFAULT 0 NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D1F55203D ON post (topic_id)');
        $this->addSql('COMMENT ON COLUMN post.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Create like table
        $this->addSql('CREATE TABLE "like" (
            id SERIAL NOT NULL,
            user_id INT NOT NULL,
            post_id INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B34B89032C ON "like" (post_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_post ON "like" (user_id, post_id)');
        $this->addSql('COMMENT ON COLUMN "like".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B34B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Create media table
        $this->addSql('CREATE TABLE media (
            id SERIAL NOT NULL,
            post_id INT DEFAULT NULL,
            file_path VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10C4B89032C ON media (post_id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Create follow table
        $this->addSql('CREATE TABLE follow (
            id SERIAL NOT NULL,
            follower_id INT NOT NULL,
            following_id INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_68344470AC24F853 ON follow (follower_id)');
        $this->addSql('CREATE INDEX IDX_683444701816E3A3 ON follow (following_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_follower_following ON follow (follower_id, following_id)');
        $this->addSql('COMMENT ON COLUMN follow.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_683444701816E3A3 FOREIGN KEY (following_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Drop all tables in reverse order
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT FK_68344470AC24F853');
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT FK_683444701816E3A3');
        $this->addSql('DROP TABLE follow');

        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C4B89032C');
        $this->addSql('DROP TABLE media');

        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B3A76ED395');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B34B89032C');
        $this->addSql('DROP TABLE "like"');

        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D1F55203D');
        $this->addSql('DROP TABLE post');

        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE "user"');
    }
}

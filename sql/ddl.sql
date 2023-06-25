DROP TABLE IF EXISTS users;
CREATE TABLE users
(
    user_id               INT AUTO_INCREMENT PRIMARY KEY,
    firstname             VARCHAR(255) NOT NULL,
    lastname              VARCHAR(255) NOT NULL,
    registration_datetime TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email_address         VARCHAR(255) NOT NULL,
    password_hash         VARCHAR(255) NOT NULL,
    role_id               INT          NOT NULL,
    CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles (role_id)
);

DROP TABLE IF EXISTS roles;
CREATE TABLE roles
(
    role_id   INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS categories;
CREATE TABLE categories
(
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS items;
CREATE TABLE items
(
    item_id                INT AUTO_INCREMENT PRIMARY KEY,
    item_name              VARCHAR(255)   NOT NULL,
    item_description       VARCHAR(255)   NOT NULL,
    category_id            INT            NOT NULL,
    seller_id              INT            NOT NULL,
    minimum_bid            DECIMAL(13, 2) NOT NULL DEFAULT 1.00,
    starting_price         DECIMAL(13, 2) NOT NULL DEFAULT 0.00,
    reserve_price          DECIMAL(13, 2) NOT NULL DEFAULT 0.00, # item only sold if this is reached when auction ends
    auction_start_datetime DATETIME       NOT NULL,
    auction_end_datetime   DATETIME       NOT NULL,
    auction_end_notified   BOOLEAN        NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_item_seller FOREIGN KEY (seller_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_item_category FOREIGN KEY (category_id) REFERENCES categories (category_id)
        ON UPDATE CASCADE,
    INDEX (auction_start_datetime, auction_end_datetime)
);

DROP TABLE IF EXISTS bids;
CREATE TABLE bids
(
    item_id         INT            NOT NULL,
    buyer_id        INT            NOT NULL,
    bid_price       DECIMAL(13, 2) NOT NULL,
    bid_datetime    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    outbid_notified BOOLEAN        NOT NULL DEFAULT FALSE,
    PRIMARY KEY (item_id, bid_price),
    CONSTRAINT fk_bid_item FOREIGN KEY (item_id) REFERENCES items (item_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_bid_buyer FOREIGN KEY (buyer_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX (buyer_id, item_id, bid_price)
);

DROP TABLE IF EXISTS item_watches;
CREATE TABLE item_watches
(
    buyer_id INT NOT NULL,
    item_id  INT NOT NULL,
    PRIMARY KEY (buyer_id, item_id),
    CONSTRAINT fk_watch_buyer FOREIGN KEY (buyer_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_watch_item FOREIGN KEY (item_id) REFERENCES items (item_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

DROP TABLE IF EXISTS item_recommendations;
CREATE TABLE item_recommendations
(
    buyer_id   INT NOT NULL,
    item_id    INT NOT NULL,
    item_score INT NOT NULL,
    PRIMARY KEY (buyer_id, item_id),
    CONSTRAINT fk_recommendation_buyer FOREIGN KEY (buyer_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_recommendation_item FOREIGN KEY (item_id) REFERENCES items (item_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications
(
    notification_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT          NOT NULL,
    message          VARCHAR(255) NOT NULL,
    is_read          BOOLEAN      NOT NULL DEFAULT FALSE,
    created_datetime TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);


SET GLOBAL event_scheduler = ON;
CREATE EVENT refresh_recommendations
    ON SCHEDULE
        EVERY 1 DAY
            STARTS '2021-11-01 04:00:00'
    DO
    BEGIN
        TRUNCATE item_recommendations;
        INSERT INTO item_recommendations
            (SELECT buyer_bids.buyer_id,
                    similar_buyers_bids.item_id AS recommended_items,
                    COUNT(*)                    AS item_score
             FROM bids buyer_bids
                      INNER JOIN bids similar_buyers # other buyers who bought the same items
                                 ON buyer_bids.item_id = similar_buyers.item_id
                                     AND similar_buyers.buyer_id != buyer_bids.buyer_id
                      INNER JOIN bids similar_buyers_bids # items bought by similar buyers
                                 ON similar_buyers.buyer_id = similar_buyers_bids.buyer_id
                      JOIN items i
                           ON similar_buyers_bids.item_id = i.item_id AND
                              i.auction_end_datetime > NOW() # filter ended products
             WHERE NOT EXISTS( # filter out items already bought by the buyer
                     SELECT *
                     FROM bids
                     WHERE buyer_bids.buyer_id = bids.buyer_id
                       AND similar_buyers_bids.item_id = bids.item_id
                 )
             GROUP BY buyer_bids.buyer_id, similar_buyers_bids.item_id);
    END;
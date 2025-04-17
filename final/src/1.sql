ALTER TABLE booking_additional_items
ADD CONSTRAINT fk_booking
FOREIGN KEY (booking_id) REFERENCES bookings(id),
ADD CONSTRAINT fk_item
FOREIGN KEY (additional_item_id) REFERENCES additional_items(id);

ALTER TABLE booking_food_items
ADD CONSTRAINT fk_booking_food
FOREIGN KEY (booking_id) REFERENCES bookings(id),
ADD CONSTRAINT fk_food_item
FOREIGN KEY (food_item_id) REFERENCES food_items(id);